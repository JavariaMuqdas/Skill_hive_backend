<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeLanguage extends Model
{
    protected $table = 'resume_languages';
    protected $fillable = [
        'student_id',
        'language',
        'proficiency',
    ];
}
