<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Product::all();
    $products = [];
    foreach ($items as $value) {
      $value->linking();
      $products[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'products' => $products,
    ]);
  }
}
