<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\Mail;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class ExchangeController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all(Request $request) {
    $items = Exchange::where([
      ['name', '=', 'users-transfer'],
      ['from_wallet_id', '=', $request->user()->wallet_id],
    ])->get();
    $exhcnages = [];
    foreach ($items as $item) {
      $item->linking(true);
      $exhcnages[$item->id] = $item;
    }
    return $this->apiSuccessResponse('successfully gettin exchanges', [
      'exchanges' => $exhcnages,
    ]);
  }

  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
      'balance' => 'required|numeric',
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'messages' => $validator->errors(),
      ]);
    }

    $user = $request->user();
    $user->linking();

    $target_user = User::whereEmail($request->email)->first();
    if(is_null($target_user) || $request->email == $user->email) return $this->apiErrorResponse('Invalid email', [
      'errors' => ['email' => 'Invalid email']
    ]);
    $target_user->linking();
    if($user->wallet->status != 'active') return $this->apiErrorResponse('Your wallet is not activeted');
    if($target_user->wallet->status != 'active') return $this->apiErrorResponse('The target user wallet is not activeted', [
      'errors' => ['email' => 'The target user wallet is not activeted']
    ]);
    if($user->wallet->balance < $request->balance) return $this->apiErrorResponse('You don\'t have required balance');

    $exchange = Exchange::create([
      'name' => 'users-transfer',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => $target_user->wallet_id,
      'sended_balance' => $request->balance,
      'received_balance' => $request->balance,
    ]);
    $exchange->linking();
    $res = $exchange->accept();
    if(!$res['success']) {
      return $this->apiErrorResponse($res['message']);
    }

    $platformCurrency = Currency::find(Setting::find('platform_currency_id')->value[0]);

    Notification::create([
      'name' => 'notifications',
      'title' => 'Balance received',
      'message' => "$user->firstname $user->lastname sent you balance ($request->balance $platformCurrency->char)",
      'from_id' => $user->id,
      'from_model' => User::class,
      'to_id' => $target_user->id,
      'to_model' => User::class,
      'data' => [
        'event_name' => 'balance-received',
        'data' => json_encode([
          'user_id' => $user->id,
          'balance' => $request->balance,
        ])
      ],
      'image_id' => 'logo',
      'type' => 'emitOrNotify',
    ]);
    Mail::create([
      'title' => 'Credit Received',
      'template_id' => Setting::userCreditReceiveEmailTemplateId(),
      'data' => [
        '<-from->' => $user->email,
        '<-balance->' => $request->balance,
        '<-exchange_id->' => $exchange->id,
        '<-datetime->' => $exchange->created_at,
      ],
      'targets' => [$target_user->id],
      'unreades' => Admin::unreades(),
    ]);

    return $this->apiSuccessResponse('Successfully exchanging');
  }

}
