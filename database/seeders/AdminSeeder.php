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
      'username' => 'Walid Rebbouh',
      'email' => 'walidrebbouh8@gmail.com',
      'phone' => '0698858865',
      'profile_image_id' => 'admin_profile_default',
      'password' => Hash::make('Walid@1994'),
      'permissions' => [
        'users' => ['read', 'update', 'delete'],
        'currencies' => ['create', 'read', 'update', 'delete'],
        'files' => ['create', 'read', 'update', 'delete'],
        'products' => ['read', 'delete'],
        'transfers' => ['read', 'update', 'delete'],
      ],
    ]);
  }
}
