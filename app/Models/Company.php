<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'phone',
        'website',
        'industry',
        'description',
        'logo',
        'company_size',
        'city',
        'country',
        'founded_year',
        'short_description',
        'overview',
        'why_join_us',
        'total_employees',
        'dept_it',
        'dept_hr',
        'dept_marketing',
        'dept_sales',
        'dept_finance',
        'dept_operations',
        'company_email',
        'linkedin_url',
        'status',       
        'block_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->hasMany(\App\Models\Project::class);
    }
    public function jobs()
    {
        return $this->hasMany(\App\Models\Job::class);
    }
    
}
