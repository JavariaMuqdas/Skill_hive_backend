<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterApplicationsStatusEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE applications MODIFY status ENUM('applied','in_review','accepted','rejected') NOT NULL DEFAULT 'applied'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE applications MODIFY status ENUM('applied','accepted','rejected') NOT NULL DEFAULT 'applied'");
    }
}
