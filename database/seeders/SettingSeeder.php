<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    Setting::create([
      'name' => 'commission',
      'value' => [0.05],
    ]);
    Setting::create([
      'name' => 'email_verification_template_id',
      'value' => ['email-verification'],
    ]);
    Setting::create([
      'name' => 'user_recharge_template_id',
      'value' => ['user-recharge'],
    ]);
    Setting::create([
      'name' => 'user_withdraw_template_id',
      'value' => ['user-withdraw'],
    ]);
    Setting::create([
      'name' => 'user_credit_receive_template_id',
      'value' => ['credit-receive'],
    ]);
    Setting::create([
      'name' => 'user_identity_confirm_template_id',
      'value' => ['identity-confirm'],
    ]);
    Setting::create([
      'name' => 'services_status',
      'value' => ['transfers' => 'active', 'offers' => 'active', 'store' => 'active'],
    ]);
  }
}
