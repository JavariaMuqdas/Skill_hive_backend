<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeSkill extends Model
{
    protected $fillable = [
        'student_id',
        'skill',
        'level',
    ];
}
