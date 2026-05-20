<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_group_id',
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'meeting_link',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function studyGroup()
    {
        return $this->belongsTo(StudyGroup::class);
    }
}
