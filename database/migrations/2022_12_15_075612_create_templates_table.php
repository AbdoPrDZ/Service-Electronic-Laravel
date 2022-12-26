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
    Schema::create('templates', function (Blueprint $table) {
      $table->id();
      $table->longText('content');
      $table->json('args')->default('{}');
      $table->enum('for_what', ['mail', 'export', '*'])->default('*');
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
    Schema::dropIfExists('templates');
  }
};
