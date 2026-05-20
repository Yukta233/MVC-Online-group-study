<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyNoteController extends Controller
{
    public function update(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note = StudyNote::firstOrCreate(
            ['study_group_id' => $group->id],
            ['content' => '# Initial Note Workspace', 'last_edited_by' => Auth::id()]
        );

        $note->update([
            'content' => $request->content,
            'last_edited_by' => Auth::id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notes saved successfully.',
                'last_edited_by_name' => Auth::user()->name,
                'updated_at' => $note->updated_at->format('h:i:s A'),
            ]);
        }

        return back()->with('success', 'Notes saved successfully.');
    }
}
