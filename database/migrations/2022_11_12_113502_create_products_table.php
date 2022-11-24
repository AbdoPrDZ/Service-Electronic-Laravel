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
      $table->double('price');
      $table->enum('pricing_type', ['unit', 'kg', 'm', 'l', 'gb'])->default('unit');
      $table->integer('category_id');
      $table->json('tags');
      $table->json('images_ids');
      $table->text('description');
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
    Schema::dropIfExists('products');
  }
};
