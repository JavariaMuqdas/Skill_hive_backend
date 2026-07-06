<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('company_name');
            $table->string('phone');
            $table->string('website')->nullable();
            $table->string('industry');
            $table->text('description');

            $table->timestamps();
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->string('status')->default('pending'); // pending, approved, blocked
            $table->text('block_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
}
