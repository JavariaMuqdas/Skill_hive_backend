<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagePathToCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
{
    Schema::table('certificates', function (Blueprint $table) {
        $table->string('image_path')->nullable()->after('issue_date');
    });
}

public function down(): void
{
    Schema::table('certificates', function (Blueprint $table) {
        $table->dropColumn('image_path');
    });
}
}
