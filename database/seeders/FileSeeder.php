<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    File::create([
      'name' => 'admin_profile_default',
      'disk' => 'admin',
      'path' => 'defaults/user.png',
      'type' => 'image',
    ]);
    File::create([
      'name' => 'api_profile_default',
      'disk' => 'api',
      'path' => 'defaults/user.png',
      'type' => 'image',
    ]);
    File::create([
      'name' => 'logo',
      'disk' => 'public',
      'path' => 'defaults/logo.png',
      'type' => 'image',
    ]);
    File::create([
      'name' => 'transfers',
      'disk' => 'public',
      'path' => 'defaults/transfers.png',
      'type' => 'image',
    ]);
    File::create([
      'name' => 'offers',
      'disk' => 'public',
      'path' => 'defaults/offers.png',
      'type' => 'image',
    ]);
    File::create([
      'name' => 'store',
      'disk' => 'public',
      'path' => 'defaults/store.png',
      'type' => 'image',
    ]);
  }
}
