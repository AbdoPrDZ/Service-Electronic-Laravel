<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller {
  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Category::all();
    $categories = [];
    foreach ($items as $value) {
      $categories[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'categories' => $categories,
    ]);
  }

}
