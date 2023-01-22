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
    Schema::create('offers', function (Blueprint $table) {
      $table->id();
      $table->string('image_id');
      $table->json('title');
      $table->json('description');
      $table->json('sub_offers')->default('[]');
      $table->json('fields');
      $table->json('data');
      $table->json('detect_steps');
      $table->json('prices');
      $table->json('unreades')->default('[]');
      $table->boolean('is_deleted')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('offers');
  }
};