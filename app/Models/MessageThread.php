<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageThread extends Model
{
  protected $fillable = ['project_id','company_id','student_id'];
}
