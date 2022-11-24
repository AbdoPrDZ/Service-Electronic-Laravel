<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use Illuminate\Http\Request;
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

  public function changeStatus(Request $request) {
    $validator = Validator::make($request->all(), [
      'id' => 'required|integer',
      'status' => 'required|string',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }
    $transfer = Transfer::find($request->id);
    if(!is_null($transfer)) {
      if($request->status == 'eccepted' || $request->status == 'refused' || $request->status == 'checking') {
        $transfer->status = $request->status;
        $transfer->save();
        return $this->apiSuccessResponse('Successfully changing status');
      } else {
        return Controller::apiErrorResponse('Invalid status');
      }
    } else {
      return Controller::apiErrorResponse('Invalid id');
    }
  }

}
