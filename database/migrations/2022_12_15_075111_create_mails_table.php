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
      $table->integer('template_id');
      $table->string('manager');
      $table->json('data')->default('{}');
      $table->json('targets');
      $table->json('attachments')->default('[]');
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
