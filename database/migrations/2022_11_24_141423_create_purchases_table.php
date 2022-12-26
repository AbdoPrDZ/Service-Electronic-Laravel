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
      $table->integer('user_id');
      $table->string('fullname');
      $table->string('phone');
      $table->string('address')->nullable();
      $table->enum('delivery_type', ['office', 'home']);
      $table->double('total_price');
      $table->integer('delivery_cost_exchange_id');
      $table->integer('product_price_exchange_id');
      $table->json('delivery_steps')->default(json_encode([
        'seller_accept' => [
          'readed_at' => null,
          'ansower' => null,
        ],
        'location_steps' => [
          'charging' => null,
          'out_from_state' => null,
          'in_to_state' => null,
          'discharging_on_office' => null,
          'delivering_to_client' => null,
        ],
        'receive' => null,
      ]));
      $table->enum('status', ['waiting', 'received', 'seller_accept', 'client_accept', 'seller_refuse', 'client_refuse'])->default('waitting');
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
    Schema::dropIfExists('purchases');
  }
};
