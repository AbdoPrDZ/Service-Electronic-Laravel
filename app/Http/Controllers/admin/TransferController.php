<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Mail;
use App\Models\Setting;
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
  }

  public function changeStatus(Request $request, Transfer $transfer) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|in:accepted,refused',
      'description' => '',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' => $validator->errors()]);
    }
    $answerRes = $transfer->answer($request->status, $request->description, $request->user()->id);
    if(!$answerRes['success']) {
      return $this->apiErrorResponse($answerRes['message']);
    }
    if(in_array($transfer->for_what, ['recharge', 'withdraw'])) {
      $titles = [
        'recharge' => [
          'You recharged your account',
          Setting::userRechargeEmailTemplateId(),
          [
            '<-sended_balance->' => "{$transfer->sended_balance} {$transfer->sended_currency->char}",
            '<-received_balance->' => "{$transfer->received_balance} {$transfer->received_currency->char}",
            '<-received_currency->' => $transfer->received_currency->name,
            '<-sended_currency->' => $transfer->sended_currency->name,
            '<-wallet->' => $transfer->wallet,
            '<-recharge_date->' => $transfer->exchange->answered_at,
            '<-answer->' => $request->status,
            '<-answer_description->' => $request->description ?? $request->status,
          ]
        ],
        'withdraw' => [
          'You withdraw from your account',
          Setting::userWithdrawEmailTemplateId(),
          [
            '<-sended_balance->' => "{$transfer->sended_balance} {$transfer->sended_currency->char}",
            '<-received_balance->' => "{$transfer->received_balance} {$transfer->received_currency->char}",
            '<-received_currency->' => $transfer->received_currency->name,
            '<-sended_currency->' => $transfer->sended_currency->name,
            '<-to_wallet->' => $transfer->wallet,
            '<-withdraw_date->' => $transfer->exchange->answered_at,
            '<-answer->' => $request->status,
            '<-answer_description->' => $request->description ?? $request->status,
          ]
        ],
      ];
      Mail::create([
        'title' => $titles[$transfer->for_whet][0],
        'template_id' => $titles[$transfer->for_whet][1],
        'data' => $titles[$transfer->for_whet][2],
        'targets' => [$transfer->user_id],
        'unreades' => Admin::unreades(),
      ]);
    }
    return $this->apiSuccessResponse('Successfully changing status');
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
