<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('currencies', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->string('char', 10);
      $table->string('image_id');
      $table->boolean('proof_is_required')->default(true);
      $table->enum('image_pick_type', ['gallery', 'camera'])->default('gallery');
      $table->string('wallet')->nullable();
      $table->json('data')->default('{}');
      $table->string('platform_wallet_id');
      $table->json('prices')->default('{}');
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
    Schema::dropIfExists('currencies');
  }
};
