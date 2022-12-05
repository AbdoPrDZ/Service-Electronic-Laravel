<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\File;
use App\Models\Setting;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class TransferController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all(Request $request) {
    $data = Transfer::where('user_id', '=', $request->user()->id)->get();
    $items = [];
    foreach ($data as $item) {
      $item->linking();
      $items[$item->id] = $item;
    }
    return [
      'success' => true,
      'message' => 'Successfully getting transfers',
      'currencies' => $items,
    ];
  }

  public function getNextId() {
    $id = 0;
    $last = Transfer::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
      'sended_balance' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
      // 'received_balance' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
      'sended_currency_id' => 'required|string',
      'received_currency_id' => 'required|string',
      'wallet' => 'string',
      'proof' => 'file|mimes:jpg,png,jpeg'
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'messages' => $validator->errors(),
      ]);
    }
    $sended_currency = Currency::find($request->sended_currency_id);
    $received_currency = Currency::find($request->sended_currency_id);
    if(is_null($sended_currency)) return $this->apiErrorResponse('Invalid sended currency');
    if(is_null($received_currency)) return $this->apiErrorResponse('Invalid received currency');
    if($received_currency->proof_is_required && is_null($request->file('proof'))) {
      return $this->apiErrorResponse('proof image has been required.');
    }

    $user = $request->user();
    $user->linking();

    $received_balance = $sended_currency->prices[$request->received_currency_id] * $request->sended_balance;

    $transferId = $this->getNextId();
    $values = [
      'id' => $transferId,
      'user_id' => $user->id,
      'sended_balance' => $request->sended_balance,
      'received_balance' => $received_balance,
      'sended_currency_id' => $request->sended_currency_id,
      'received_currency_id' => $request->received_currency_id,
      'wallet' => $request->wallet,
    ];

    $recherge_currencies = Setting::find('recherge_currencies')->value;
    $platformCurrency = Setting::find('platform_currency_id')->value[0];
    if(in_array($request->sended_currency_id, $recherge_currencies) &&
       $request->received_currency_id == $platformCurrency) {
      if($user->wallet->status != 'active') {
        return $this->apiErrorResponse('Your wallet is not activeted');
      }
      $exchange = Exchange::create([
        'from_id' => $request->user()->id,
        'from_model' => User::class,
        'to_id' => 1,
        'to_model' => Admin::class,
        'from_wallet_id' => $sended_currency->platform_wallet_id,
        'to_wallet_id' => $user->wallet_id,
        'balance' => $received_balance
      ]);
      $values['exchange_id'] = $exchange->id;
      $user->wallet->checking_balance += $received_balance;
      $user->wallet->unlinkingAndSave();
      $values['wallet'] = null;
    }

    if(!is_null($request->file('proof'))) {
      if(!Storage::disk('api')->exists('users_data')) {
        Storage::disk('api')->makeDirectory('users_data');
      }
      $userFilesPath = "users_data/".$request->user()->id;
      if(!Storage::disk('api')->exists($userFilesPath)) {
        Storage::disk('api')->makeDirectory($userFilesPath);
        Storage::disk('api')->makeDirectory("$userFilesPath/transfers");
      }
      $allpath = Storage::disk('api')->path("$userFilesPath/transfers");
      $shortPath = "$userFilesPath/transfers/t-$transferId";
      $request->file('proof')->move($allpath, "t-$transferId");
      File::create([
        'name' => "u-" . $request->user()->id . "-t-$transferId",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $values['proof_id'] = "u-".$request->user()->id . "-t-$transferId";
    }

    Transfer::create($values);

    return $this->apiSuccessResponse('Successfully add transfer');
  }


}
