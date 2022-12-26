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
    // File::create([
    //   'name' => 'admin_profile_default',
    //   'disk' => 'admin',
    //   'path' => 'defaults/user.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'api_profile_default',
    //   'disk' => 'api',
    //   'path' => 'defaults/user.png',
    //   'type' => 'image',
    // ]);
    File::create([
      'name' => 'currency-1',
      'disk' => 'public',
      'path' => 'currencies/1.png',
      'type' => 'image',
    ]);
    File::create([
      'name' => 'currency-2',
      'disk' => 'public',
      'path' => 'currencies/2.png',
      'type' => 'image',
    ]);
    // File::create([
    //   'name' => 'currency-3',
    //   'disk' => 'public',
    //   'path' => 'currencies/3.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-4',
    //   'disk' => 'public',
    //   'path' => 'currencies/4.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-5',
    //   'disk' => 'public',
    //   'path' => 'currencies/5.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-6',
    //   'disk' => 'public',
    //   'path' => 'currencies/6.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-7',
    //   'disk' => 'public',
    //   'path' => 'currencies/7.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-9',
    //   'disk' => 'public',
    //   'path' => 'currencies/9.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-10',
    //   'disk' => 'public',
    //   'path' => 'currencies/10.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-11',
    //   'disk' => 'public',
    //   'path' => 'currencies/11.png',
    //   'type' => 'image',
    // ]);
    // File::create([
    //   'name' => 'currency-12',
    //   'disk' => 'public',
    //   'path' => 'currencies/12.png',
    //   'type' => 'image',
    // ]);
  }
}
