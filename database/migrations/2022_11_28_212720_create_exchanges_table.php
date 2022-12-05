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
      $table->integer('from_id');
      $table->string('from_model')->default(User::class);
      $table->integer('to_id');
      $table->string('to_model')->default(User::class);
      $table->string('from_wallet_id');
      $table->string('to_wallet_id');
      $table->double('balance');
      $table->enum('status', ['waiting', 'received', 'blocked'])->default('waiting');
      $table->string('anower_description')->nullable();
      $table->timestamp('anower_at')->nullable();
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
