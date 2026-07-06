<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingUpdate extends Model
{
  protected $fillable = [
    'project_id','company_id','student_id','title','note','progress','created_by_user_id'
  ];
}
