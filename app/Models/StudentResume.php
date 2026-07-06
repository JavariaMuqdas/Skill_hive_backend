<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentResume extends Model
{
    protected $table = 'student_resumes';

    protected $fillable = [
        'student_id',
        'summary',
        'address',
        'linkedin',
        'github',
        'portfolio',
        'default_template',
        'privacy',
        'phone',
        'dob',
        'gender',
        'hobbies',
    ];

    protected $casts = [
        'hobbies' => 'array',
        'dob' => 'date:Y-m-d',
    ];
}