<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('projects', function (Blueprint $table) {
      $table->id();

      // assuming companies are users with role=company OR separate companies table
      $table->unsignedBigInteger('company_id');

      $table->string('title');
      $table->string('category');
      $table->string('city');
      $table->enum('job_type', ['onsite', 'remote', 'hybrid'])->default('onsite');

      $table->json('required_skills')->nullable();

      $table->integer('salary_min')->nullable();
      $table->integer('salary_max')->nullable();

      $table->date('deadline')->nullable();

      $table->text('description');

      $table->string('attachment_path')->nullable();

      $table->enum('status', ['draft', 'published', 'closed'])->default('draft');

      $table->timestamps();

      $table->index('company_id');
    });
  }

  public function down(): void {
    Schema::dropIfExists('projects');
  }
};
