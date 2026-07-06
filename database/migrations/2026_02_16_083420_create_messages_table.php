<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    if (Schema::hasTable('messages')) return;

    Schema::create('messages', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('thread_id');
      $table->unsignedBigInteger('sender_user_id'); // users.id
      $table->text('body');
      $table->timestamps();

      $table->foreign('thread_id')->references('id')->on('message_threads')->onDelete('cascade');
      $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  public function down(): void {
    Schema::dropIfExists('messages');
  }
};
