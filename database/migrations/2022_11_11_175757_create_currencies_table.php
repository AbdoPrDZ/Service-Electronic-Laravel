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
      $table->double('max_receive');
      $table->boolean('proof_is_required')->default(true);
      $table->enum('image_pick_type', ['gallery', 'camera'])->default('gallery');
      $table->string('wallet');
      $table->string('platform_wallet_id');
      $table->json('prices')->default('{}');
      $table->json('unreades')->default('[]');
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
