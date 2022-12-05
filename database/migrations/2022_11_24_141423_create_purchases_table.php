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
    Schema::create('purchases', function (Blueprint $table) {
      $table->id();
      $table->integer('product_id');
      $table->integer('count')->default(1);
      $table->string('fullname');
      $table->string('phone');
      $table->string('address')->nullable();
      $table->enum('delivery_type', ['office', 'home']);
      $table->double('delivery_price');
      $table->timestamp('charging_date')->nullable();
      $table->timestamp('delivery_date')->nullable();
      $table->timestamp('received_date')->nullable();
      $table->double('total_price');
      $table->string('pay_exchange_id')->nullable();
      $table->enum('status', ['payed', 'returned', 'canceld']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('purchases');
  }
};
