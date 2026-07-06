<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            // ✅ Link certificate to logged-in student/user
            $table->unsignedBigInteger('user_id');

            $table->string('title');
            $table->string('issuer')->nullable();
            $table->date('date')->nullable();

            // ✅ image path stored in DB (optional)
            $table->string('image_path')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};