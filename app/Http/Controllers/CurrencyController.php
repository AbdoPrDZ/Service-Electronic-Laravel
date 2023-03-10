<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Setting;

class CurrencyController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all() {
    if(!Setting::serviceIsActive('transfers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $data = Currency::whereIsDeleted('0')->get();
    $items = [];
    foreach ($data as $item) {
      $items[$item->id] = $item;
      $items[$item->id]->linking();
    }
    return [
      'success' => true,
      'message' => 'Successfully getting currencies',
      'currencies' => $items,
    ];
  }
}
