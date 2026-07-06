<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeProject extends Model
{
    protected $table = 'resume_projects';
    protected $fillable = [
        'student_id',
        'name',
        'tech_stack',
        'link',
        'description',
    ];

    protected $casts = [
        'description' => 'array',
    ];
}
