<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
  protected $fillable = [
    'project_id',
    'company_id',
    'student_id',
    'cover_letter',
    'status',
    'remarks',
    'interview_marks',
    'final_score',
    'interview_date',
    'interview_time',
  ];
  protected $table='Applications';

  public function project()
  {
    return $this->belongsTo(Project::class,'project_id', 'id');
  }
  public function company()
  {
    return $this->belongsTo(Company::class);
  }
  public function student()
  {
    return $this->belongsTo(Student::class);
  }
}
