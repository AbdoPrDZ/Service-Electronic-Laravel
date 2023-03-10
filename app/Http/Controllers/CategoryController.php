<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;

class CategoryController extends Controller {
  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all() {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $data = Category::whereIsDeleted('0')->get();
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
