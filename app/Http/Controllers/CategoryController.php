<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller {
  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all() {
    $data = Category::all();
    $items = [];
    foreach ($data as $item) {
      $items[$item->id] = $item;
    }
    return [
      'success' => true,
      'message' => 'Successfully getting cateories',
      'currencies' => $items,
    ];
  }
}
