<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScoresToApplicationsTable extends Migration
{
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            // Integer type columns add kar rahe hain
            $table->integer('interview_marks')->nullable()->after('status');
            $table->integer('final_score')->nullable()->after('interview_marks');
        });
    }

    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['interview_marks', 'final_score']);
        });
    }
}
