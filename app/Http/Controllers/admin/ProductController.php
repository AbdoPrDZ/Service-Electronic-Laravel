<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Product::where('is_deleted', '=', 0)->get();
    $products = [];
    foreach ($items as $value) {
      $value->linking();
      $products[$value->id] = $value;
    }
    $items = Category::where('is_deleted', '=', 0)->get();
    $categories = [];
    foreach ($items as $value) {
      $categories[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => [
        'products' => $products,
        'categories' => $categories,
      ],
    ]);
  }

  static function news($admin_id) {
    return count(Product::news($admin_id)) + count(Category::news($admin_id));
  }

  static function readNews($admin_id) {
    Product::readNews($admin_id);
    Category::readNews($admin_id);
    return Controller::apiSuccessResponse('successfully reading news');
  }
}
