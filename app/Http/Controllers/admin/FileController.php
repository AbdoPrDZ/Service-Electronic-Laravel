<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = File::all();
    $files = [];
    foreach ($items as $value) {
      $files[$value->name] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'files' => $files,
    ]);
  }

}
