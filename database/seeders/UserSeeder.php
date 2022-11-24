<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    User::create([
      'firstname' => 'abdo',
      'lastname' => 'pr',
      'email' => 'abdopr47@gmail.com',
      'phone' => '0778185797',
      'password' => Hash::make('123456'),
      'profile_image_id' => 'api_profile_default',
    ]);
  }
}
