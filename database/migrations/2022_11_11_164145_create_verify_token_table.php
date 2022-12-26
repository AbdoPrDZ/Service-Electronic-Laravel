<?php

use App\Models\User;
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
    Schema::create('verify_token', function (Blueprint $table) {
      $table->string('token')->primary();
      $table->string('code');
      $table->string('user_id');
      $table->string('model')->default(User::class);
      $table->timestamp('used_at')->nullable();
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
    Schema::dropIfExists('verify_token');
  }
};
