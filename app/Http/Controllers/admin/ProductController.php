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
    $items = Product::all();
    $products = [];
    foreach ($items as $value) {
      $value->linking();
      $products[$value->id] = $value;
    }
    $items = Category::all();
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

  static function news(Request $request) {
    $admin_id = $request->user()->id;
    return [
      'count' => count(Product::news($admin_id)) + count(Category::news($admin_id)),
    ];
  }

  static function readNews(Request $request) {
    Product::readNews($request->user()->id);
    Category::readNews($request->user()->id);
    return Controller::apiSuccessResponse('successfully reading news');
  }
}
