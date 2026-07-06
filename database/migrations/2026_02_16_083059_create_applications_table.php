<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    if (Schema::hasTable('applications')) return;

    Schema::create('applications', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('project_id');
      $table->unsignedBigInteger('company_id'); // from projects.company_id
      $table->unsignedBigInteger('student_id'); // from students.id
      $table->text('cover_letter')->nullable();
      $table->enum('status', ['applied','in_review','rejected','accepted'])->default('applied');
      $table->timestamps();

      $table->unique(['project_id','student_id']);

      $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
      $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
    });
  }

  public function down(): void {
    Schema::dropIfExists('applications');
  }
};
