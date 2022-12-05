<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder {

  public function createCurrencyWallet($id) {
    Wallet::create([
      'id' => $id,
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => 0,
      'total_received_balance' => 0,
      'total_withdrawn_balance' => 0,
      'status' => 'active',
      'answored_at' => Carbon::now(),
    ]);
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-1");
    Currency::create([
      'id' => 1,
      'name' => 'Payssera',
      'char' => '€',
      'max_receive' => 200,
      'wallet'=> 'thowalid16@gmail.com',
      'platform_wallet_id' => $wallet_id,
      'prices'=> [
         '1' => ['d' => 1 , 'w' => 0.9], // payssera
         '2' => ['d' => 2 , 'w' => 1], // webmony
         '3' => ['d' => 0.9 , 'w' => 0.8], // prfect
         '9' => ['d' => 220 , 'w' => 200], // ccp
         '12' => ['d' => 220 , 'w' => 200], // ccp
      ],
    ]);
    $this->createCurrencyWallet($wallet_id);
    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-2");
    Currency::create([
      'id' => 2,
      'name' => 'Webmony',
      'char' => '$',
      'max_receive' => 30000,
      'wallet'=> 'thowalid16@gmail.com',
      'platform_wallet_id' => $wallet_id,
      'prices'=> [
        '1' => ['d' => 1 , 'w' => 0.9], // payssera
        '2' => ['d' => 2 , 'w' => 1], // webmony
        '3' => ['d' => 0.9 , 'w' => 0.8], // prfect
        '9' => ['d' => 220 , 'w' => 200], // ccp
        '12' => ['d' => 220 , 'w' => 200], // ccp
      ],
    ]);
    $this->createCurrencyWallet($wallet_id);
    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-3");
    Currency::create([
      'id' => 3,
      'name' => 'Prfect Mony',
      'char' => '$',
      'max_receive' => 30000,
      'wallet'=> 'thowalid16@gmail.com',
      'platform_wallet_id' => $wallet_id,
      'prices'=> [
        '1' => ['d' => 1 , 'w' => 0.9], // payssera
        '2' => ['d' => 2 , 'w' => 1], // webmony
        '3' => ['d' => 0.9 , 'w' => 0.8], // prfect
        '9' => ['d' => 220 , 'w' => 200], // ccp
        '12' => ['d' => 220 , 'w' => 200], // ccp
      ],
    ]);
    $this->createCurrencyWallet($wallet_id);
    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-9");
    Currency::create([
      'id' => 9,
      'name' => 'CCP',
      'char' => 'DZD',
      'max_receive' => 30000,
      'wallet'=> '0023528701 cle 33',
      'platform_wallet_id' => $wallet_id,
      'prices'=> [
        '1' => ['d' => 1 , 'w' => 0.9], // payssera
        '2' => ['d' => 2 , 'w' => 1], // webmony
        '3' => ['d' => 0.9 , 'w' => 0.8], // prfect
      ],
    ]);
    $this->createCurrencyWallet($wallet_id);
    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-12");
    Currency::create([
      'id' => 12,
      'name' => 'Service Electronic',
      'char' => 'SE',
      'max_receive' => 30000,
      'wallet'=> '0023528701 cle 33',
      'platform_wallet_id' => $wallet_id,
      'proof_is_required' => false,
      'prices'=> [
        '1' => ['d' => 1 , 'w' => 0.9], // payssera
        '2' => ['d' => 2 , 'w' => 1], // webmony
        '3' => ['d' => 0.9 , 'w' => 0.8], // prfect
        '9' => ['d' => 220 , 'w' => 200], // ccp
        '12' => ['d' => 220 , 'w' => 200], // ccp
      ],
    ]);
    $this->createCurrencyWallet($wallet_id);
  }
}
