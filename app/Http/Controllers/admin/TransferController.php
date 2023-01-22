<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Validator;

class TransferController extends Controller {

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

  static function news($admin_id) {
    return count(Transfer::news($admin_id, 'transfer'));
  }

  static function readNews($admin_id) {
    Transfer::readNews($admin_id, 'transfer');
    return Controller::apiSuccessResponse('successfully reading news');
  }

  public function changeStatus(Request $request, $transfer) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|string',
      'description' => '',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }
    if(in_array($request->status, ['accepted', 'refused'])) {
      $answerRes = $transfer->answer($request->status, $request->description, $request->user()->id);
      if(!$answerRes['success']) {
        return $this->apiErrorResponse($answerRes['message']);
      }
      return $this->apiSuccessResponse('Successfully changing status');
    } else {
      return $this->apiErrorResponse('Invalid status');
    }
  }

  public function delete(Request $request, $transfer) {
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
      File::find($transfer->proof_id)->delete();
    }
    $transfer->delete();
    return $this->apiSuccessResponse('Successflully deleting transfer');
  }

}
