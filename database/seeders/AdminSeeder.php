<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $wallet_id = bin2hex('w-' . date_format(now(), 'yyyy-MM-dd') . "-c-1");
    Admin::create([
      'id' => 1,
      'username' => 'Walid Rebbouh',
      'email' => 'thowalid16@gmail.com',
      'phone' => '0698858865',
      'profile_image_id' => 'admin_profile_default',
      'wallet_id' => $wallet_id,
      'password' => Hash::make('Walid@1994'),
      'permissions' => [
        'users' => ['read', 'update', 'delete'],
        'currencies' => ['create', 'read', 'update', 'delete'],
        'files' => ['create', 'read', 'update', 'delete'],
        'products' => ['read', 'delete'],
        'transfers' => ['read', 'update', 'delete'],
      ],
    ]);
    Wallet::create([
      'id' => $wallet_id ,
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => 0,
      'status' => 'active',
      'answored_at' => now(),
    ]);
  }
}
