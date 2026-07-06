<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    Schema::table('student_resumes', function (Blueprint $table) {
        if (!Schema::hasColumn('student_resumes', 'hobbies')) {
            $table->json('hobbies')->nullable()->after('portfolio');
        }
    });
}

    public function down()
{
    Schema::table('student_resumes', function (Blueprint $table) {
        if (Schema::hasColumn('student_resumes', 'hobbies')) {
            $table->dropColumn('hobbies');
        }
    });
}
};