<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\File;
use App\Models\Setting;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class TransferController extends Controller {

  public function all(Request $request, $target) {
    if(!Setting::serviceIsActive('transfers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $data = Transfer::where([['user_id', '=', $request->user()->id], ['for_what', '=', $target]])->get();
    $items = [];
    foreach ($data as $item) {
      $item->linking();
      $items[$item->id] = $item;
    }
    return $this->apiSuccessResponse('Successfully getting transfers', ['transfers' => $items]);
  }

  public function create(Request $request) {
    if(!Setting::serviceIsActive('transfers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $request->merge(['data' => $this->tryDecodeArray($request->data)]);
    $validator = Validator::make($request->all(), [
      'received_balance' => 'required|numeric',
      'received_currency_id' => 'required|string',
      'sended_currency_id' => 'required|string',
      'data' => 'required|array',
      'proof' => 'file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
        'data' => $request->data,
        'data_is_array' => is_array($request->data),
      ]);
    }
    $received_balance = floatVal($request->received_balance);

    $sended_currency = Currency::find($request->sended_currency_id);
    if(is_null($sended_currency)) return $this->apiErrorResponse('Invalid sended currency', [
      'errors' => ['sended_currency_id' => 'Invalid sended currency'],
    ]);

    $received_currency = Currency::find($request->received_currency_id);
    if(is_null($received_currency)) return $this->apiErrorResponse('Invalid received currency', [
      'errors' => ['received_currency_id' => 'Invalid received currency'],
    ]);
    $received_currency->linking();

    $rules = [];
    foreach ($received_currency->data as $name => $item) {
      $rules[$name] = $item['validate'];
    }
    $validator = Validator::make($request->data, $rules);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $platformCurrency = Setting::platformCurrency();
    $platformCurrency->linking();

    if($sended_currency->proof_is_required && is_null($request->file('proof'))) {
      return $this->apiErrorResponse('Proof image has been required', [
        'errors' => ['proof' => 'Proof image has been required'],
      ]);
    }

    if(!in_array($request->sended_currency_id, array_keys($received_currency->prices)) ||
      in_array($platformCurrency->id , [$request->sended_currency_id, $request->received_currency_id])
    ) {
      return $this->apiErrorResponse('You can\'t transfer to this currency', [
        'errors' => ['received_currency_id' => 'You can\'t transfer to this currency']
      ]);
    }

    if($received_balance > $received_currency->platform_wallet->balance) {
      return $this->apiErrorResponse('Sorry we don\'t have credit required, you can try again later');
    }

    $user = $request->user();
    $user->linking();

    $sended_balance = $received_currency->prices[$request->sended_currency_id]['buy'] * $received_balance;

    $transferId = Transfer::getNextSequenceValue();
    $values = [
      'id' => $transferId,
      'user_id' => $user->id,
      'sended_balance' => $sended_balance,
      'received_balance' => $received_balance,
      'sended_currency_id' => $request->sended_currency_id,
      'received_currency_id' => $request->received_currency_id,
      'data' => $request->data,
      'for_what' => 'transfer',
      'unreades' => Admin::unreades()
    ];

    $exchange = Exchange::create([
      'name' => 'user-transfer',
      'from_wallet_id' => $received_currency->platform_wallet_id,
      'sended_balance' => $received_balance,
      'received_balance' => $sended_balance,
    ]);
    $values['exchange_id'] = $exchange->id;

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
      $imageFile = File::create([
        'name' => "u-" . $request->user()->id . "-t-$transferId",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $values['proof_id'] = $imageFile->name;
    }

    Transfer::create($values);

    return $this->apiSuccessResponse('Successfully add transfer');
  }

  public function createRecharge(Request $request) {
    if(!Setting::serviceIsActive('transfers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $validator = Validator::make($request->all(), [
      'sended_balance' => 'required|numeric',
      'sended_currency_id' => 'required|string',
      'proof' => 'file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }
    $sended_balance = floatVal($request->sended_balance);
    if($sended_balance == 0) {
      return $this->apiErrorResponse('The sended balance can\'t be zero', [
        'errors' => ['sended_balance' => 'The sended balance can\'t be zero']
      ]);
    }

    $sended_currency = Currency::find($request->sended_currency_id);
    if(is_null($sended_currency)) return $this->apiErrorResponse('Invalid sended currency', [
      'errors' => ['sended_currency_id' => 'Invalid sended currency']
    ]);

    $platformCurrency = Setting::platformCurrency();
    $platformCurrency->linking();

    if($sended_currency->proof_is_required && is_null($request->file('proof'))) {
      return $this->apiErrorResponse('Proof image has been required', [
        'errors' => ['proof' => 'Proof image has been required']
      ]);
    }

    if(!in_array($platformCurrency->id, array_keys($sended_currency->prices))) {
      return $this->apiErrorResponse('You can\'t transfer to this currency', [
        'errors' => ['received_currency_id' => 'You can\'t transfer to this currency']
      ]);
    }

    $user = $request->user();
    $user->linking();

    $received_balance = $sended_currency->prices[$request->received_currency_id]['sell'] * $sended_balance;

    $transferId = Transfer::getNextSequenceValue();
    $values = [
      'id' => $transferId,
      'user_id' => $user->id,
      'sended_balance' => $sended_balance,
      'received_balance' => $received_balance,
      'sended_currency_id' => $request->sended_currency_id,
      'received_currency_id' => $platformCurrency->id,
      'data' => [],
      'for_what' => 'recharge',
      'unreades' => Admin::unreades()
    ];

    if($user->wallet->status != 'active') {
      return $this->apiErrorResponse('Your wallet is not activeted');
    }
    $values['received_balance'] = $received_balance;
    $exchange = Exchange::create([
      'name' => 'user-recharge',
      'to_wallet_id' => $user->wallet_id,
      'sended_balance' => $sended_balance,
      'received_balance' => $received_balance,
    ]);
    $values['exchange_id'] = $exchange->id;

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
      $imageFile = File::create([
        'name' => "u-" . $request->user()->id . "-t-$transferId",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $values['proof_id'] = $imageFile->name;
    }

    Transfer::create($values);

    return $this->apiSuccessResponse('Successfully add transfer');
  }

  public function createWithdraw(Request $request) {
    if(!Setting::serviceIsActive('transfers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $request->merge(['data' => $this->tryDecodeArray($request->data)]);
    $validator = Validator::make($request->all(), [
      'received_balance' => 'required|numeric',
      'received_currency_id' => 'required|string',
      'data' => 'required|array',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $received_balance = floatVal($request->received_balance);
    if($received_balance == 0) {
      return $this->apiErrorResponse('Received balance can\'t be zero');
    }

    $received_currency = Currency::find($request->received_currency_id);
    if(is_null($received_currency)) return $this->apiErrorResponse('Invalid received currency');
    $received_currency->linking();

    $platformCurrency = Setting::platformCurrency();
    $platformCurrency->linking();

    $rules = [];
    foreach ($received_currency->data as $name => $item) {
      $rules[$name] = $item['validate'];
    }
    $validator = Validator::make($request->data, $rules);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    if(!in_array($platformCurrency->id, array_keys($received_currency->prices))) {
      return $this->apiErrorResponse('You can\'t transfer to this currency');
    }

    $user = $request->user();
    $user->linking();

    $sended_balance = $received_currency->prices[$platformCurrency->id]['buy'] * $received_balance;
    if($sended_balance > $user->balance) {
      return $this->apiErrorResponse('Your balance is not enough');
    }

    if($received_balance > $received_currency->platform_wallet->balance) {
      return $this->apiErrorResponse('Sorry we don\'t have credit required, you can try again later');
    }

    $transferId = Transfer::getNextSequenceValue();
    $values = [
      'id' => $transferId,
      'user_id' => $user->id,
      'sended_balance' => $sended_balance,
      'received_balance' => $received_balance,
      'sended_currency_id' => $platformCurrency->id,
      'received_currency_id' => $request->received_currency_id,
      'data' => $request->data,
      'for_what' => 'withdraw',
      'unreades' => Admin::unreades()
    ];

    if($user->wallet->status != 'active') {
      return $this->apiErrorResponse('Your wallet is not activeted');
    }
    $user->wallet->balance -= $sended_balance;
    $user->wallet->checking_withdraw_balance += $sended_balance;
    $user->wallet->unlinkingAndSave();

    $exchange = Exchange::create([
      'name' => 'user-withdraw',
      'from_wallet_id' => $received_currency->platform_wallet_id,
      'sended_balance' => $received_balance,
      'received_balance' => $sended_balance,
    ]);
    $values['exchange_id'] = $exchange->id;

    Transfer::create($values);

    return $this->apiSuccessResponse('Successfully add transfer');
  }

}
