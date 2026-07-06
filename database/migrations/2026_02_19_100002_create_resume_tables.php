<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) student_resumes (1 row per student)
        Schema::create('student_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); // Corrected for consistency

            $table->text('summary')->nullable();
            $table->string('address')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('portfolio')->nullable();

            $table->enum('privacy', ['public', 'private'])->default('private');
            $table->enum('default_template', ['classic', 'modern', 'minimal'])->default('classic');

            $table->timestamps();
        });

        // 2) resume_educations (many)
        Schema::create('resume_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); // Fixed foreign key

            $table->string('degree')->nullable();
            $table->string('institute')->nullable();
            $table->string('field')->nullable();
            $table->string('grade')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();
        });

        // 3) resume_experiences (many)
        Schema::create('resume_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); // Fixed foreign key

            $table->string('title')->nullable();
            $table->string('company')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);

            $table->text('responsibilities')->nullable();

            $table->timestamps();
        });

        // 4) resume_skills (many)
        Schema::create('resume_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); // Fixed foreign key

            $table->string('skill');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->nullable();

            $table->timestamps();
        });

        // 5) resume_projects (many)
        Schema::create('resume_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); // Fixed foreign key

            $table->string('name')->nullable();
            $table->string('tech_stack')->nullable();
            $table->string('link')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        // 6) resume_languages (many)
        Schema::create('resume_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); // Fixed foreign key

            $table->string('language');
            $table->string('proficiency')->nullable(); // e.g. Native / Fluent / Intermediate

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_languages');
        Schema::dropIfExists('resume_projects');
        Schema::dropIfExists('resume_skills');
        Schema::dropIfExists('resume_experiences');
        Schema::dropIfExists('resume_educations');
        Schema::dropIfExists('student_resumes');
    }
};