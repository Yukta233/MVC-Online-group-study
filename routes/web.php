<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudyGroupController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\StudyNoteController;
use App\Http\Controllers\StudySessionController;
use App\Http\Controllers\StudyTaskController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminOrModerator;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Redirect root to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tasks', [DashboardController::class, 'tasks'])->name('global.tasks');
    Route::post('/tasks', [StudyTaskController::class, 'storeGlobal'])->name('global.tasks.store');
    Route::get('/sessions', [DashboardController::class, 'sessions'])->name('global.sessions');
    Route::get('/search', [DashboardController::class, 'search'])->name('global.search');
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('global.notifications');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


    // Study Groups
    Route::post('/groups/create', [StudyGroupController::class, 'create'])->name('groups.create');
    Route::post('/groups/join', [StudyGroupController::class, 'join'])->name('groups.join');
    Route::get('/groups/{id}', [StudyGroupController::class, 'show'])->name('groups.show');

    // Chat Room
    Route::post('/groups/{id}/chat', [ChatMessageController::class, 'send'])->name('chat.send');
    Route::get('/groups/{id}/chat/poll', [ChatMessageController::class, 'poll'])->name('chat.poll');

    // Resource Drive
    Route::post('/groups/{id}/resources', [ResourceController::class, 'store'])->name('resources.store');
    Route::delete('/resources/{id}', [ResourceController::class, 'destroy'])->name('resources.destroy');
    Route::post('/groups/{id}/whiteboard/snapshot', [ResourceController::class, 'storeSnapshot'])->name('resources.storeSnapshot');

    // Collaborative Notepad
    Route::post('/groups/{id}/notes', [StudyNoteController::class, 'update'])->name('notes.update');

    // Meeting Scheduler
    Route::post('/groups/{id}/sessions', [StudySessionController::class, 'store'])->name('sessions.store');

    // Kanban Tasks
    Route::post('/groups/{id}/tasks', [StudyTaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/{id}/status', [StudyTaskController::class, 'updateStatus'])->name('tasks.updateStatus');

    // Quizzes & Flashcards
    Route::post('/groups/{id}/quizzes', [QuizController::class, 'storeQuiz'])->name('quizzes.store');
    Route::post('/quizzes/{id}/submit', [QuizController::class, 'submitQuizAttempt'])->name('quizzes.submit');
    Route::post('/groups/{id}/flashcards', [QuizController::class, 'storeFlashcard'])->name('flashcards.store');
    Route::delete('/flashcards/{id}', [QuizController::class, 'destroyFlashcard'])->name('flashcards.destroy');

    // Admin & Moderator panel
    Route::middleware(AdminOrModerator::class)->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/users/{id}/role', [AdminController::class, 'updateRole'])->name('admin.users.updateRole');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::delete('/groups/{id}', [AdminController::class, 'destroyGroup'])->name('admin.groups.destroy');
        Route::delete('/resources/{id}', [AdminController::class, 'destroyResource'])->name('admin.resources.destroy');
    });
});
