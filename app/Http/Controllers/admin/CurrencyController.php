<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\File;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Validator;

class CurrencyController extends Controller {

  static function all(Request $request) {
    $items = Currency::where('is_deleted', '=', 0)->get();
    $currencies = [];
    foreach ($items as $value) {
      $currencies[$value->id] = $value;
      $currencies[$value->id]->linking();
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => $currencies,
    ]);
  }

  static function news($admin_id) {
    return count(Currency::news($admin_id));
  }

  static function readNews($admin_id) {
    Currency::readNews($admin_id);
  }

  public function create(Request $request) {
    $request->merge(['proof_is_required' => $request->proof_is_required == 'true']);
    try {
      $request->merge(['balance' => floatval($request->balance)]);
    } catch (\Throwable $th) {
      $request->merge(['balance' => null]);
    }
    $request->merge(['data' => $this->tryDecodeArray($request->data)]);
    $request->merge(['prices' => $this->tryDecodeArray($request->prices)]);
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'char' => 'required|string',
      'balance' => 'numeric',
      'wallet' => 'string',
      'data' => 'array',
      'image' => 'required|file|mimes:jpg,png,jpeg',
      'proof_is_required' => 'required|boolean',
      'prices' => 'array',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $oldCurrency = Currency::whereRaw("LOWER(`name`) = '" . strtolower($request->name) . "'")->first();
    if(!is_null($oldCurrency)) return $this->apiErrorResponse('This currency name already exists');

    try {
      $prices = [];
      foreach ($request->prices as $price) {
        if($price->currency_id != -1) {
          $currency = Currency::find($price->currency_id);
          if(is_null($currency)) {
            return $this->apiErrorResponse("Invalid currency id #$price->currency_id");
          }
        }
        $prices[$price->currency_id] = [
          'buy' => floatVal($price->buy_price),
          'sell' => floatVal($price->sell_price),
        ];
      }
    } catch (\Throwable $th) {
      return $this->apiErrorResponse('Invalid Prices');
    }

    $currencyId = Currency::getNextSequenceValue();

    if(!Storage::disk('public')->exists("currencies")) {
      Storage::disk('public')->makeDirectory("currencies");
    }
    $time = now()->timestamp;
    $request->file('image')->move(Storage::disk('public')->path("currencies"), "currency-$currencyId-$time.png");
    $imageFile = File::create([
      'name' => "currency-$currencyId-$time",
      'disk' => 'public',
      'type' => 'image',
      'path' => "currencies/currency-$currencyId-$time.png",
    ]);

    $walletId = bin2hex('w-' . date_format(now(), 'yyyy-MM-dd') . "-c-$currencyId");
    Wallet::create([
      'id' => $walletId,
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => $request->balance,
      'status' => 'active',
      'answored_at' => now(),
    ]);

    Currency::create([
      'id' => $currencyId,
      'name' => $request->name,
      'char' => $request->char,
      'image_id' => $imageFile->name,
      'prices' => $prices,
      'platform_wallet_id' => $walletId,
      'wallet' => $request->wallet,
      'data' => $request->data ? $this->listMap2Map($request->data) : [],
      'proof_is_required' => $request->proof_is_required,
      'unreades' => Admin::unreades($request->user()->id)
    ]);
    return $this->apiSuccessResponse('Succesfully creating currency');
  }

  public function edit(Request $request, Currency $currency) {
    $request->merge(['proof_is_required' => !is_null($request->proof_is_required) ? $request->proof_is_required == 'true' : null]);
    try {
      $request->merge(['balance' => floatval($request->balance)]);
    } catch (\Throwable $th) {
      $request->merge(['balance' => null]);
    }
    $request->merge(['data' => $this->tryDecodeArray($request->data)]);
    $validator = Validator::make($request->all(), [
      'name' => 'string',
      'char' => 'string',
      'wallet' => 'string',
      'data' => 'array',
      'image' => 'file|mimes:jpg,png,jpeg',
      'proof_is_required' => 'boolean',
      'prices' => 'string',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    if(strcmp(strtolower($request->name), strtolower($currency->name)) !== 0) {
      $oldCurrency = Currency::whereRaw("LOWER(`name`) = '" . strtolower($request->name) . "'")->first();
      if(!is_null($oldCurrency)) return $this->apiErrorResponse('This currency name already exists', [$currency->id]);
      $currency->name = $request->name;
    } else {
      unset($currency->name);
    }
    $prices = [];
    if(!is_null($request->prices)) {
      try {
        $rPrices = json_decode($request->prices);
        foreach ($rPrices as $price) {
          if(is_null(Currency::find($price->currency_id))) {
            return $this->apiErrorResponse("Invalid currency id #$price->currency_id");
          }
          $prices[$price->currency_id] = [
            'buy' => floatVal($price->buy_price),
            'sell' => floatVal($price->sell_price),
          ];
        }
        $currency->prices = $prices;
      } catch (\Throwable $th) {
        return $this->apiErrorResponse('Invalid Prices');
      }
    }

    $currency->char = $request->char ?? $currency->char;
    $currency->wallet = $request->wallet;
    $currency->data = $this->listMap2Map($request->data) ?? $currency->data;
    $currency->proof_is_required = $request->proof_is_required ?? $currency->proof_is_required;

    if($request->file('image')) {
      File::find($currency->image_id)?->delete();
      if(!Storage::disk('public')->exists("currencies")) {
        Storage::disk('public')->makeDirectory("currencies");
      }
      $time = now()->timestamp;
      $request->file('image')->move(Storage::disk('public')->path("currencies"), "currency-$currency->id-$time.png");
      $imageFile = File::create([
        'name' => "currency-$currency->id-$time",
        'disk' => 'public',
        'type' => 'image',
        'path' => "currencies/currency-$currency->id-$time.png",
      ]);
      $currency->image_id = $imageFile->name;
    }
    $currency->save();
    $currency->linking();
    $currency->platform_wallet->balance = $request->balance ?? $currency->platform_wallet->balance;
    $currency->unreades = Admin::unreades($request->user()->id);
    $currency->platform_wallet->save();

    return $this->apiSuccessResponse('Succesfully editing currency');
  }

  public function delete(Request $request, $currency) {
    $currency->delete();
    return $this->apiSuccessResponse('Successflully deleting currency');
  }

}
