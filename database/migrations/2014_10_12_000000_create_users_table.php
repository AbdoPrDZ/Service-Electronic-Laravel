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
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('firstname');
      $table->string('lastname');
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('phone')->unique();
      $table->string('password');
      $table->string('wallet_id')->nullable()->unique();
      $table->json('verification_images_ids')->default('[]');
      $table->timestamp('identity_verifited_at')->nullable();
      $table->string('profile_image_id')->default('api_profile_default');
      $table->string('messaging_token')->nullable()->unique();
      $table->rememberToken();
      $table->json('settings')->default('[]');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('users');
  }
};
