<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Exchange;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class ExchangeController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
      'balance' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
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
    if(is_null($target_user) || $request->email == $user->email) return $this->apiErrorResponse('Invalid email');
    $target_user->linking();
    if($user->wallet->status != 'active') return $this->apiErrorResponse('Your wallet is not activeted');
    if($target_user->wallet->status != 'active') return $this->apiErrorResponse('The target user wallet is not activeted');
    if($user->wallet->balance < $request->balance) return $this->apiErrorResponse('You don\'t have required balance');

    $exchange = Exchange::create([
      'name' => 'users-transfer',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => $target_user->wallet_id,
      'sended_balance' => $request->balance,
      'received_balance' => $request->balance,
      'status' => 'received',
      'answered_at' => Carbon::now(),
    ]);
    $exchange->linking();
    $exchange->eccept();

    // $user->wallet->balance -= $request->balance;
    // $user->wallet->unlinkingAndSave();
    // $target_user->wallet->balance += $request->balance;
    // $target_user->wallet->unlinkingAndSave();


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
      'image_id' => 'currency-4',
      'type' => 'emitOrNotify',
    ]);

    return $this->apiSuccessResponse('Successfully exchanging');
  }

}
