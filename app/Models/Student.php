<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\StudentResume;
use App\Models\ResumeSkill;
use App\Models\ResumeEducation;
use App\Models\ResumeExperience;
use App\Models\ResumeProject;
use App\Models\ResumeLanguage;
use App\Models\Certificate;

class Student extends Model
{
    public function applications()
{
    return $this->hasMany(Application::class);
}
    // Laravel default table name "students" hota hai,
    // but explicitly rakhna ho to:
    protected $table = 'students';

    // old profile columns aap delete kar chuki ho, is liye fillable sirf safe rakhein
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // OPTIONAL relations (CV builder)
    public function resumeBase()
    {
        return $this->hasOne(StudentResume::class, 'student_id');
    }

    public function resumeEducations()
    {
        return $this->hasMany(ResumeEducation::class, 'student_id');
    }

    public function resumeExperiences()
    {
        return $this->hasMany(ResumeExperience::class, 'student_id');
    }

    public function resumeSkills()
    {
        return $this->hasMany(ResumeSkill::class, 'student_id');
    }

    public function resumeProjects()
    {
        return $this->hasMany(ResumeProject::class, 'student_id');
    }

    public function resumeLanguages()
    {
        return $this->hasMany(ResumeLanguage::class, 'student_id');
    }
    // public function certificates()
    // {
    //     return $this->hasMany(Certificate::class, 'student_id');
    // }
}
