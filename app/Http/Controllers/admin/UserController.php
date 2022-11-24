<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = User::all();
    $users = [];
    foreach ($items as $value) {
      $value->linking();
      $users[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'users' => $users,
    ]);
  }

}
