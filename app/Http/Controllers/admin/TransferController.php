<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Validator;

class TransferController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Transfer::all();
    $transfers = [];
    foreach ($items as $value) {
      $value->linking();
      $transfers[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'transfers' => $transfers,
    ]);
  }

  public function changeStatus(Request $request, $id) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|string',
      'description' => 'string',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }
    $transfer = Transfer::find($id);
    if(!is_null($transfer)) {
      if(in_array($request->status, ['accepted', 'refused'])) {
        $ansowerRes = $transfer->ansower($request->status, $request->description);
        if(!$ansowerRes['success']) {
          return $this->apiErrorResponse($ansowerRes['message']);
        }
        return $this->apiSuccessResponse('Successfully changing status');
      } else {
        return Controller::apiErrorResponse('Invalid status');
      }
    } else {
      return Controller::apiErrorResponse('Invalid id');
    }
  }

}
