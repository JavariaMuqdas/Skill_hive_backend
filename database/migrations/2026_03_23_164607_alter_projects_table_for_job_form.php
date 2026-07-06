<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('projects', 'hiring_locations')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->json('hiring_locations')->nullable();
            });
        }

        if (!Schema::hasColumn('projects', 'job_requirements')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->json('job_requirements')->nullable();
            });
        }

        if (!Schema::hasColumn('projects', 'employment_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->json('employment_type')->nullable();
            });
        }

        if (!Schema::hasColumn('projects', 'working_schedule')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('working_schedule')->nullable();
            });
        }

        if (!Schema::hasColumn('projects', 'salary_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->enum('salary_type', ['hourly', 'custom'])->default('hourly');
            });
        }

        if (!Schema::hasColumn('projects', 'hourly_rate')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->decimal('hourly_rate', 10, 2)->nullable();
            });
        }

        if (!Schema::hasColumn('projects', 'salary_negotiable')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('salary_negotiable')->default(false);
            });
        }

        if (!Schema::hasColumn('projects', 'hiring_multiple_candidates')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('hiring_multiple_candidates')->default(false);
            });
        }

        if (!Schema::hasColumn('projects', 'attachment_path')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('attachment_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        //
    }
};