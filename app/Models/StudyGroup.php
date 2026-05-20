<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'subject',
        'access_code',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function studyNote()
    {
        return $this->hasOne(StudyNote::class);
    }

    public function studySessions()
    {
        return $this->hasMany(StudySession::class)->orderBy('scheduled_at', 'asc');
    }

    public function studyTasks()
    {
        return $this->hasMany(StudyTask::class)->orderBy('created_at', 'desc');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class)->orderBy('created_at', 'desc');
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class)->orderBy('created_at', 'desc');
    }
}
