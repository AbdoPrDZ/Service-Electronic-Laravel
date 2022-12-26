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
    Schema::create('transfers', function (Blueprint $table) {
      $table->id();
      $table->string('user_id');
      $table->double('sended_balance');
      $table->double('received_balance');
      $table->integer('sended_currency_id');
      $table->integer('received_currency_id');
      $table->string('proof_id')->nullable();
      $table->string('wallet')->nullable();
      $table->integer('exchange_id')->nullable();
      $table->enum('for_what', ['transfer', 'withdraw', 'recharge'])->default('transfer');
      $table->enum('status', ['accepted', 'refused', 'checking'])->default('checking');
      $table->timestamp('answered_at')->nullable();
      $table->string('answer_description')->nullable();
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
    Schema::dropIfExists('transfer');
  }
};
