<?php

namespace Database\Seeders;

use App\Models\Admin;
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
    Admin::create([
      'id' => 1,
      'username' => 'admin',
      'email' => 'admin@admin.com',
      'phone' => '0778185797',
      'profile_image_id'=> 'admin-logo',
      'password'=> \Hash::make('123456'),
      'permissions'=> json_encode([
        'users' => ['read', 'update', 'delete'],
        'currencies' => ['create', 'read', 'update', 'delete'],
        'files' => ['create', 'read', 'update', 'delete'],
        'products' => ['read', 'delete'],
        'transfers' => ['read', 'update', 'delete'],
      ]),
    ]);
  }
}
