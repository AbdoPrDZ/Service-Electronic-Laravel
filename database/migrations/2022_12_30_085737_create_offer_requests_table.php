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
    Schema::create('offer_requests', function (Blueprint $table) {
      $table->id();
      $table->integer('user_id');
      $table->integer('offer_id');
      $table->string('sub_offer')->nullable();
      $table->json('fields');
      $table->json('data')->nullable();
      $table->enum('status', ['waiting_admin_accept', 'admin_accept', 'admin_refuse'])->default('waiting_admin_accept');
      $table->double('total_price');
      $table->integer('exchange_id');
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
    Schema::dropIfExists('offer_requests');
  }
};
