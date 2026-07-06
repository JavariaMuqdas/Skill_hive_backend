<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // 1. Status field: Jo save karega company approved hai, pending hai ya blocked
            // Default status hum 'pending' rakhein ge jab new company register ho
            $table->string('status')->default('pending')->after('website'); 

            // 2. Block Reason field: Jo modal se select kiya hua reason ya additional note save karega
            $table->text('block_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['status', 'block_reason']);
        });
    }
};