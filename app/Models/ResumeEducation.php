<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeEducation extends Model
{
    protected $table = 'resume_educations';
    protected $fillable = [
        'student_id',
        'degree',
        'institute',
        'field',
        'grade',
        'start_date',
        'end_date',
        'description',
    ];
}
