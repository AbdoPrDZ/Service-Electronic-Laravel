<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\File;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Validator;

class CurrencyController extends Controller {
  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Currency::all();
    $currencies = [];
    foreach ($items as $value) {
      $currencies[$value->id] = $value;
      $currencies[$value->id]->linking();
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => $currencies,
    ]);
  }

  static function news(Request $request) {
    return [
      'count' => count(Currency::news($request->user()->id)),
    ];
  }

  static function readNews(Request $request) {
    Currency::readNews($request->user()->id);
    return Controller::apiSuccessResponse('successfully reading news');
  }

  public function getNextId() {
    $id = 0;
    $last = Currency::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  public function create(Request $request) {
    $request->merge(['proof_is_required' => $request->proof_is_required == 'true']);
    try {
      $request->merge(['balance' => floatval($request->balance)]);
    } catch (\Throwable $th) {
      $request->merge(['balance' => null]);
    }
    try {
      $request->merge(['max_receive' => floatval($request->max_receive)]);
    } catch (\Throwable $th) {
      $request->merge(['max_receive' => null]);
    }
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'char' => 'required|string',
      'balance' => 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
      'max_receive' => 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
      'wallet' => 'required|string',
      'image' => 'required|file|mimes:jpg,png,jpeg',
      'proof_is_required' => 'required|boolean',
      'prices' => 'required|string',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $oldCurrency = Currency::whereRaw("LOWER(`name`) = '" . strtolower($request->name) . "'")->first();
    if(!is_null($oldCurrency)) return $this->apiErrorResponse('This currency name already exists');

    try {
      $rPrices = json_decode($request->prices);
      $prices = [];
      foreach ($rPrices as $price) {
        if($price->currency_id != -1) {
          $currency = Currency::find($price->currency_id);
          if(is_null($currency)) {
            return $this->apiErrorResponse("Invalid currnecy id #$price->currency_id");
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

    $currencyId = $this->getNextId();

    if(!Storage::disk('public')->exists("currencies")) {
      Storage::disk('public')->makeDirectory("currencies");
    }
    $request->file('image')->move(Storage::disk('public')->path("currencies"), "$currencyId.png");
    File::create([
      'name' => "currency-$currencyId",
      'disk' => 'public',
      'type' => 'image',
      'path' => "currencies/$currencyId.png",
    ]);

    $walletId = bin2hex('w-' . date_format(Carbon::now(), 'yyyy-MM-dd') . "-c-$currencyId");
    Wallet::create([
      'id' => $walletId,
      'user_id' => 1,
      'user_model' => Admin::class,
      'balance' => $request->balance,
      'total_received_balance' => 0,
      'total_withdrawn_balance' => 0,
      'status' => 'active',
      'answored_at' => Carbon::now(),
    ]);

    Currency::create([
      'id' => $currencyId,
      'name' => $request->name,
      'char' => $request->char,
      'prices' => $prices,
      'platform_wallet_id' => $walletId,
      'max_receive' => $request->max_receive,
      'wallet' => $request->wallet,
      'proof_is_required' => $request->proof_is_required,
    ]);
    return $this->apiSuccessResponse('Succesfully creating currency');
  }

  public function edit(Request $request, $id) {
    $request->merge(['proof_is_required' => !is_null($request->proof_is_required) ? $request->proof_is_required == 'true' : null]);
    try {
      $request->merge(['max_receive' => !is_null($request->max_receive) ? floatval($request->max_receive) : null]);
    } catch (\Throwable $th) {
      $request->merge(['max_receive' => null]);
    }
    $validator = Validator::make($request->all(), [
      'name' => 'string',
      'char' => 'string',
      'max_receive' => 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
      'wallet' => 'string',
      'image' => 'file|mimes:jpg,png,jpeg',
      'proof_is_required' => 'boolean',
      'prices' => 'string',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $currency = Currency::find($id);
    if(strcmp(strtolower($request->name), strtolower($currency->name)) !== 0) {
      $oldCurrency = Currency::whereRaw("LOWER(`name`) = '" . strtolower($request->name) . "'")->first();
      if(!is_null($oldCurrency)) return $this->apiErrorResponse('This currency name already exists', [$id]);
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
            return $this->apiErrorResponse("Invalid currnecy id #$price->currency_id");
          }
          // $prices[$price->currency_id] = floatval($price->price);
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

    $currency->char = $request->char;
    $currency->max_receive = $request->max_receive;
    $currency->wallet = $request->wallet;
    $currency->proof_is_required = $request->proof_is_required;
    $currency->save();
    $currency->linking();
    $currency->platform_wallet->balance = $request->balance;
    $currency->platform_wallet->save();

    if($request->file('image')) {
      if(!Storage::disk('public')->exists("currencies")) {
        Storage::disk('public')->makeDirectory("currencies");
      }
      $request->file('image')->move(Storage::disk('public')->path("currencies"), "$id.png");
    }

    return $this->apiSuccessResponse('Succesfully editing currency');
  }

  public function delete(Request $request, $id) {
    $currency = Currency::find($id);
    if(is_null($currency)) return $this->apiErrorResponse('Invlid currency id');
    $currency->delete();
    return $this->apiSuccessResponse('Successflully deleting currency');
  }

}
