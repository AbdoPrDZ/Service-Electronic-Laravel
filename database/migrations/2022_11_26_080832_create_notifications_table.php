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
    Schema::create('notifications', function (Blueprint $table) {
      $table->id();
      $table->string('from_id')->nullable();
      $table->string('from_model')->nullable();
      $table->string('to_id');
      $table->string('to_model')->default(User::class);
      $table->string('name');
      $table->string('title');
      $table->string('message');
      $table->json('data');
      $table->string('image_id')->nullable();
      $table->boolean('is_readed')->default(false);
      $table->enum('type', ['emit', 'notify', 'emitOrNotify', 'emitAndNotify']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('notifications');
  }
};
