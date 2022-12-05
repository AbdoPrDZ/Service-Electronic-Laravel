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
    }
    return Controller::apiSuccessResponse('Success', [
      'currencies' => $currencies,
    ]);
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
    $request->merge(['proof_required' => $request->proof_required == 'true']);
    try {
      $request->merge(['max_receive' => floatval($request->max_receive)]);
    } catch (\Throwable $th) {
      $request->merge(['max_receive' => null]);
    }
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'char' => 'required|string',
      'max_receive' => 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
      'wallet' => 'required|string',
      'image' => 'required|file|mimes:jpg,png,jpeg',
      'proof_required' => 'required|boolean',
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
          'buy' => $price->buy_price,
          'sell' => $price->sell_price,
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
      'balance' => 0,
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
      'proof_is_required' => $request->proof_required,
    ]);
    return $this->apiSuccessResponse('succesfully creating currency');
  }

  public function delete(Request $request, $id) {
    $currency = Currency::find($id);
    if(is_null($currency)) return $this->apiErrorResponse('Invlid currency id');
    $currency->delete();
    return $this->apiSuccessResponse('Successflully deleting currency');
  }

}
