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
    Schema::create('sellers', function (Blueprint $table) {
      $table->id();
      $table->integer('user_id');
      $table->string('store_name');
      $table->string('store_address');
      $table->string('store_image_id')->nullable();
      $table->json('delivery_prices');
      $table->enum('status', ['checking', 'accepted', 'refused'])->default('checking');
      $table->string('anower_description')->nullable();
      $table->timestamp('answered_at')->nullable();
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
  public function down() {
    Schema::dropIfExists('sellers');
  }
};
