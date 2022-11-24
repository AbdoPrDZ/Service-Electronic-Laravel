<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller {
  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Currency::all();
    $currencies = [];
    foreach ($items as $value) {
      $currencies[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'currencies' => $currencies,
    ]);
  }
}
