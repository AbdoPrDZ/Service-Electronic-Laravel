<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {

    Wallet::create([
      'id' => '772d32323232323232322d4465634465632d303530352d632d31',
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => 0,
      'total_received_balance' => 0,
      'total_withdrawn_balance' => 0,
      'status' => 'active',
      'answored_at' => Carbon::now(),
    ]);
    Wallet::create([
      'id' => '772d32323232323232322d4465634465632d303530352d632d32',
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => 0,
      'total_received_balance' => 0,
      'total_withdrawn_balance' => 0,
      'status' => 'active',
      'answored_at' => Carbon::now(),
    ]);
    Wallet::create([
      'id' => '772d32323232323232322d4465634465632d303530352d632d33',
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => 0,
      'total_received_balance' => 0,
      'total_withdrawn_balance' => 0,
      'status' => 'active',
      'answored_at' => Carbon::now(),
    ]);
    Wallet::create([
      'id' => '772d32323232323232322d4465634465632d303530352d632d3132',
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => 0,
      'total_received_balance' => 0,
      'total_withdrawn_balance' => 0,
      'status' => 'active',
      'answored_at' => Carbon::now(),
    ]);
  }
}
