<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'category',
        'city',
        'job_type',
        'description',
        'status',
        'required_skills',
        'salary_min',
        'salary_max',
        'attachment_path',

        'hiring_locations',
        'job_requirements',
        'employment_type',
        'working_schedule',
        'deadline_date',
        'salary_type',
        'hourly_rate',
        'salary_negotiable',
        'hiring_multiple_candidates',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'hiring_locations' => 'array',
        'job_requirements' => 'array',
        'employment_type' => 'array',
        'work_mode' => 'array',
        'salary_negotiable' => 'boolean',
        'hiring_multiple_candidates' => 'boolean',
        'deadline_date' => 'date',
    ];


    protected $appends = ['attachment_url'];

    public function getAttachmentUrlAttribute()
    {
        return $this->attachment_path
            ? asset('storage/' . $this->attachment_path)
            : null;
    }

    public function company()
{
    return $this->belongsTo(Company::class, 'company_id', 'id','user_id');
}
    // Project model ke andar ye function lazmi hona chahiye
    public function applications()
    {
        // Agar aik job/project par bohat saari applications ho sakti hain
        return $this->hasMany(Application::class);
    }
    public function assignments()
{
    return $this->hasMany(ProjectAssignment::class);
}
}
