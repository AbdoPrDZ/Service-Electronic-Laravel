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
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->integer('seller_id');
      $table->string('name');
      $table->text('description');
      $table->boolean('inside_country')->default(true);
      $table->double('price');
      $table->double('commission');
      $table->integer('count');
      $table->json('rates')->default('[]');
      $table->json('likes')->default('[]');
      $table->integer('category_id');
      $table->json('images_ids');
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
    Schema::dropIfExists('products');
  }
};
