<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('mails', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->string('template_id');
      $table->json('data')->default('{}');
      $table->json('targets');
      $table->json('unreades')->default('[]');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('mails');
  }
};
