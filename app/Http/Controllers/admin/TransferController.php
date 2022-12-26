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
    $items = Transfer::trnasfers();
    $transfers = [];
    foreach ($items as $value) {
      $value->toArray();
      $transfers[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => $transfers,
    ]);
  }

  static function news(Request $request) {
    return [
      'count' => count(Transfer::news($request->user()->id, 'transfer')),
    ];
  }

  static function readNews(Request $request) {
    Transfer::readNews($request->user()->id, 'transfer');
    return Controller::apiSuccessResponse('successfully reading news');
  }

  public function changeStatus(Request $request, $id) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|string',
      'description' => '',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }
    $transfer = Transfer::find($id);
    if(!is_null($transfer)) {
      if(in_array($request->status, ['accepted', 'refused'])) {
        $ansowerRes = $transfer->ansower($request->status, $request->description, $request->user()->id);
        if(!$ansowerRes['success']) {
          return $this->apiErrorResponse($ansowerRes['message']);
        }
        return $this->apiSuccessResponse('Successfully changing status');
      } else {
        return $this->apiErrorResponse('Invalid status');
      }
    } else {
      return $this->apiErrorResponse('Invalid id');
    }
  }

  public function delete(Request $request, $id) {
    $transfer = Transfer::find($id);
    if(is_null($transfer)) return $this->apiErrorResponse('Invlid transfer id');
    $transfer->linking();
    $transfer->user->linking();
    if($transfer->status == 'checking') {
      $transfer->user->wallet->checking_recharge_balance -= $transfer->received_balance;
      if($transfer->for_what == 'withdraw') $transfer->user->wallet->balance += $transfer->received_balance;
      $transfer->user->wallet->unlinkingAndSave();
    }
    if (!is_null($transfer->exchange)) {
      $transfer->exchange->delete();
    }
    if (!is_null($transfer->proof)) {
      $transfer->proof->delete();
    }
    $transfer->delete();
    return $this->apiSuccessResponse('Successflully deleting transfer');
  }

}
