<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // drop old profile columns
            $table->dropColumn(['phone','degree','skills','resume_path']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // restore old columns if rollback
            $table->string('phone')->nullable();
            $table->string('degree')->nullable();
            $table->text('skills')->nullable();
            $table->string('resume_path')->nullable();
        });
    }
};
