<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_resumes', function (Blueprint $table) {
            if (!Schema::hasColumn('student_resumes', 'phone')) {
                $table->string('phone', 30)->nullable()->after('address');
            }
            if (!Schema::hasColumn('student_resumes', 'dob')) {
                $table->date('dob')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('student_resumes', 'gender')) {
                $table->string('gender', 20)->nullable()->after('dob');
            }
            if (!Schema::hasColumn('student_resumes', 'hobbies')) {
                $table->json('hobbies')->nullable()->after('portfolio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_resumes', function (Blueprint $table) {
            if (Schema::hasColumn('student_resumes', 'hobbies')) {
                $table->dropColumn('hobbies');
            }
            if (Schema::hasColumn('student_resumes', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('student_resumes', 'dob')) {
                $table->dropColumn('dob');
            }
            if (Schema::hasColumn('student_resumes', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};