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
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->float('sended_balance');
            $table->float('received_balance');
            $table->integer('sended_currency_id');
            $table->integer('received_currency_id');
            $table->string('proof_id')->nullable();
            $table->string('wallet');
            $table->enum('status', ['eccepted', 'refused', 'checking'])->default('checking');
            $table->timestamp('ansowerd_at')->nullable();
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
        Schema::dropIfExists('transfer');
    }
};
