<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    if (Schema::hasTable('message_threads')) return;

    Schema::create('message_threads', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('project_id');
      $table->unsignedBigInteger('company_id');
      $table->unsignedBigInteger('student_id');
      $table->timestamps();

      $table->unique(['project_id','company_id','student_id']);

      $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
      $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
    });
  }

  public function down(): void {
    Schema::dropIfExists('message_threads');
  }
};
