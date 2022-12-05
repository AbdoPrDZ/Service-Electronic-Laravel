<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    Setting::create([
      'name' => 'platform_currency_id',
      'value' => [12],
    ]);
    Setting::create([
      'name' => 'recherge_currencies',
      'value' => [1, 9],
    ]);
  }
}
