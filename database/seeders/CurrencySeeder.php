<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\File;
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
      'proof_is_required' => true,
      'image_pick_type' => 'gallery',
      'wallet' => 'thowalid16@gmail.com, Service',
      'platform_wallet_id' => $wallet_id,
      'prices' => [
        "3" => ["buy" => 220,"sell" => 200],
        "4" => ["buy" => 220,"sell" => 200]
      ],
      'unreades' => Admin::unreades(),
    ]);
    $this->createCurrencyWallet($wallet_id);

    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-2");
    Currency::create([
      'id' => 2,
      'name' => 'Wise',
      'char' => '$',
      'proof_is_required' => true,
      'image_pick_type' => 'gallery',
      'wallet' => 'thowalid16@gmail.com',
      'platform_wallet_id' => $wallet_id,
      'prices' => [
        "1" => ["buy" => 1,"sell" => 1],
        "3" => ["buy" => 220,"sell" => 200],
      ],
      'unreades' => Admin::unreades(),
    ]);
    $this->createCurrencyWallet($wallet_id);

    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-3");
    Currency::create([
      'id' => 3,
      'name' => 'CCP',
      'char' => 'DZD',
      'proof_is_required' => true,
      'image_pick_type' => 'camera',
      'wallet' => '0023528701 cle 33, Walid Rebbouh, ourgla',
      'platform_wallet_id' => $wallet_id,
      'prices' => [
        "1" => ["buy" => 0.005, "sell" => 0.0045],
        "4" => ["buy" => 1, "sell" => 1],
      ],
      'unreades' => Admin::unreades(),
    ]);
    $this->createCurrencyWallet($wallet_id);

    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-4");
    Currency::create([
      'id' => 4,
      'name' => 'Service Electronic',
      'char' => 'SE',
      'proof_is_required' => false,
      'image_pick_type' => 'camera',
      'wallet' => '0023528701 cle 33, Walid Rebbouh, ourgla',
      'platform_wallet_id' => $wallet_id,
      'prices' => [
        "1" => ["buy" => 0.005, "sell" => 0.0045],
        "4" => ["buy" => 1, "sell" => 1],
      ],
      'unreades' => Admin::unreades(),
    ]);
    $this->createCurrencyWallet($wallet_id);

    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-5");
    Currency::create([
      'id' => 5,
      'name' => 'Perfect Money',
      'char' => '$',
      'proof_is_required' => true,
      'image_pick_type' => 'gallery',
      'wallet' => 'U34081482',
      'platform_wallet_id' => $wallet_id,
      'prices' => [
        "1" => ["buy" => 0.97,"sell" => 0.9],
        "3" => ["buy" => 212,"sell" => 194],
        "4" => ["buy" => 212,"sell" => 194]
      ],
      'unreades' => Admin::unreades(),
    ]);
    $this->createCurrencyWallet($wallet_id);

    $wallet_id = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-6");
    Currency::create([
      'id' => 6,
      'name' => 'Baridi Mob',
      'char' => 'DZD',
      'proof_is_required' => true,
      'image_pick_type' => 'gallery',
      'wallet' => '00799999002966160878',
      'platform_wallet_id' => $wallet_id,
      'prices' => [
        "1" => ["buy" => 0.005, "sell" => 0.0045],
        "4" => ["buy" => 1, "sell" => 1],
      ],
      'unreades' => Admin::unreades(),
    ]);
    $this->createCurrencyWallet($wallet_id);

    for ($i=1; $i <= 6; $i++) {
      File::create([
        'name' => "currency-$i",
        'disk' => 'public',
        'path' => "currencies/$i.png",
        'type' => 'image',
      ]);
    }
  }
}
