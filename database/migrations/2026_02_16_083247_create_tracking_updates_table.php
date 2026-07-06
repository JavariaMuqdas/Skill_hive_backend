<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    if (Schema::hasTable('tracking_updates')) return;

    Schema::create('tracking_updates', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('project_id');
      $table->unsignedBigInteger('company_id');
      $table->unsignedBigInteger('student_id');
      $table->string('title');
      $table->text('note')->nullable();
      $table->unsignedTinyInteger('progress')->default(0); // 0-100
      $table->unsignedBigInteger('created_by_user_id'); // users.id
      $table->timestamps();

      $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
      $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
      $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  public function down(): void {
    Schema::dropIfExists('tracking_updates');
  }
};
