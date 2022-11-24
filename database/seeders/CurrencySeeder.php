<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    \App\Models\Currency::create([
      'id' => 1,
      'name' => 'Payssera',
      'char' => '€',
      'max_receive' => 200,
      'wallet'=> 'thowalid16@gmail.com',
      'prices'=> json_encode([
         '1' => 0.09, // payssera
         '2' => 2, // webmony
         '3' => 0.95, // prfect
         '9' => 0.00455, // ccp
      ]),
      ]);
      \App\Models\Currency::create([
      'id' => 2,
      'name' => 'Webmony',
      'char' => '$',
      'max_receive' => 30000,
      'wallet'=> 'thowalid16@gmail.com',
      'prices'=> json_encode([
         '1' => 0.09, // payssera
         '2' => 2, // webmony
         '3' => 0.95, // prfect
         '9' => 0.00455, // ccp
      ]),
      ]);
      \App\Models\Currency::create([
      'id' => 3,
      'name' => 'Prfect Mony',
      'char' => '$',
      'max_receive' => 30000,
      'wallet'=> 'thowalid16@gmail.com',
      'prices'=> json_encode([
         '1' => 0.09, // payssera
         '2' => 2, // webmony
         '3' => 0.95, // prfect
         '9' => 0.00455, // ccp
      ]),
      ]);
      \App\Models\Currency::create([
      'id' => 9,
      'name' => 'CCP',
      'char' => 'DZD',
      'max_receive' => 30000,
      'wallet'=> '0023528701 cle 33',
      'prices'=> json_encode([
         '1' => 0.09, // payssera
         '2' => 2, // webmony
         '3' => 0.95, // prfect
         '9' => 0.00455, // ccp
      ]),
      ]);
      \App\Models\Currency::create([
      'id' => 12,
      'name' => 'Service Electronic',
      'char' => 'SE',
      'max_receive' => 30000,
      'wallet'=> '0023528701 cle 33',
      'prices'=> json_encode([
         '1' => 0.09, // payssera
         '2' => 2, // webmony
         '3' => 0.95, // prfect
         '9' => 0.00455, // ccp
      ]),
      ]);
  }
}
