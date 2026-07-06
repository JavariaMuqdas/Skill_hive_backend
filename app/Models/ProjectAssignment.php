<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAssignment extends Model
{
  protected $fillable = ['project_id', 'company_id', 'student_id', 'status'];
  public function project()
  {
    return $this->belongsTo(\App\Models\Project::class);
  }
  public function student()
  {
    return $this->belongsTo(\App\Models\Student::class);
  }
  public function company()
  {
    return $this->belongsTo(\App\Models\Company::class);
  }
}
