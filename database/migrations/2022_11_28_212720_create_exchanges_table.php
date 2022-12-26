<?php

use App\Models\User;
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
    Schema::create('exchanges', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('from_wallet_id')->nullable();
      $table->string('to_wallet_id');
      $table->double('sended_balance');
      $table->double('received_balance');
      $table->enum('status', ['waiting', 'received', 'blocked'])->default('waiting');
      $table->string('anower_description')->nullable();
      $table->timestamp('answered_at')->nullable();
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
    Schema::dropIfExists('exchanges');
  }
};
