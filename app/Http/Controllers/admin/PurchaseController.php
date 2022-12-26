<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Purchase::all();
    $purchases = [];
    foreach ($items as $value) {
      $value->linking();
      $purchases[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => $purchases,
    ]);
  }

  static function news(Request $request) {
    $admin_id = $request->user()->id;
    return [
      'count' => count(Purchase::news($admin_id)),
    ];
  }

  static function readNews(Request $request) {
    Purchase::readNews($request->user()->id);
    return Controller::apiSuccessResponse('successfully reading news');
  }
}
