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
    Schema::create('wallets', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->integer('user_id');
      $table->string('user_model')->default(User::class);
      $table->double('balance')->default(0);
      $table->double('checking_recharge_balance')->default(0);
      $table->double('checking_withdraw_balance')->default(0);
      $table->enum('status', ['active', 'blocked', 'checking'])->default('checking');
      $table->timestamp('answored_at')->nullable();
      $table->string('answer_description')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('wallets');
  }
};
