<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\Flashcard;
use App\Models\StudyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function storeQuiz(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'time_limit_minutes' => 'required|integer|min:1|max:180',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:1000',
            'questions.*.correct_option' => 'required|integer|min:0|max:3',
            'questions.*.options' => 'required|array|size:4',
            'questions.*.options.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $quiz = Quiz::create([
                'study_group_id' => $group->id,
                'title' => $request->title,
                'description' => $request->description,
                'time_limit_minutes' => $request->time_limit_minutes,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->questions as $qData) {
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $qData['question_text'],
                    'options' => $qData['options'],
                    'correct_option' => $qData['correct_option'],
                ]);
            }

            DB::commit();
            return back()->with('success', 'Quiz created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create quiz: ' . $e->getMessage()]);
        }
    }

    public function submitQuizAttempt(Request $request, $quizId)
    {
        $quiz = Quiz::with('questions')->findOrFail($quizId);
        $group = StudyGroup::findOrFail($quiz->study_group_id);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'answers' => 'required|array', // key: question_id, value: selected option index
            'time_spent_seconds' => 'required|integer|min:0',
        ]);

        $score = 0;
        $totalQuestions = $quiz->questions->count();

        foreach ($quiz->questions as $question) {
            $userAnswer = $request->answers[$question->id] ?? null;
            if ($userAnswer !== null && intval($userAnswer) === $question->correct_option) {
                $score++;
            }
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'score' => $score,
            'total_questions' => $totalQuestions,
            'time_spent_seconds' => $request->time_spent_seconds,
        ]);

        return response()->json([
            'success' => true,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'percentage' => $totalQuestions > 0 ? round(($score / $totalQuestions) * 100) : 0,
            'time_spent_formatted' => gmdate("i:s", $request->time_spent_seconds),
            'attempt' => $attempt
        ]);
    }

    public function storeFlashcard(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:1000',
        ]);

        Flashcard::create([
            'study_group_id' => $group->id,
            'question' => $request->question,
            'answer' => $request->answer,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Flashcard added to deck!');
    }

    public function destroyFlashcard($id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $group = StudyGroup::findOrFail($flashcard->study_group_id);

        if ($flashcard->created_by !== Auth::id() && $group->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $flashcard->delete();
        return back()->with('success', 'Flashcard deleted successfully!');
    }
}
