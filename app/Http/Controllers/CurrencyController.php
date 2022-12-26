<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all() {
    $data = Currency::all();
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
