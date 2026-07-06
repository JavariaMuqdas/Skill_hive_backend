<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeExperience extends Model
{
    protected $table = 'resume_experiences';
    protected $fillable = [
        'student_id',
        'title',
        'company',
        'start_date',
        'end_date',
        'is_current',
        'responsibilities',
    ];

    protected $casts = [
        'responsibilities' => 'array',
        'is_current' => 'boolean',
    ];
}
