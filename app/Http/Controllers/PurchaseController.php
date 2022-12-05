<?php

namespace App\Http\Controllers;

use App\Http\SocketBridge\SocketClient;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Validator;

class PurchaseController extends Controller {
  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function encodeAddress(string $country, string $state, string $address) {
    return "$country->$state->$address";
  }

  public function decodeAddress(string $address) {
    list($country, $state, $address) = explode('->', $address);
    return [$country, $state, $address];
  }

  public function create(Request $request, $product_id) {
    $validator = Validator::make($request->all(), [
      'fullname' => 'required|string',
      'count' => 'required|integer',
      'phone' => 'required|string',
      'country' => 'required|string',
      'state' => 'required|string',
      'address' => 'required|string',
      'delivery_type' => 'required|in:home,office',
      'delivery_price' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
      'total_price' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
    ]);
    if($validator->fails()){
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $product = Product::find($product_id);
    if(is_null($product)) {
      return $this->apiErrorResponse('Invalid product Id', [], 404);
    }

    if($request->total_price > $request->user()->balance) {
      return $this->apiErrorResponse('You don\'t have required balance to buy this product');
    }

    Purchase::create([
      'user_id' => $request->user()->id,
      'fullname' => $request->fullname,
      'count' => $request->count,
      'phone' => $request->phone,
      'address' => $this->encodeAddress($request->country, $request->state, $request->address),
      'delivery_type' => $request->delivery_type,
      'delivery_price' => $request->delivery_price,
      'total_price' => $request->total_price,
    ]);

    // $request->user()->balance = $request->user()->balance -
    //                             $request->total_price;
    // $product->seller->balance = $product->seller->balance +
    //                             $request->total_price -
    //                             ($request->total_price * config('app.app_settings.products_commission'));

    (new SocketClient($request->user()->id))->emitOrNotify('product-buy-seccess', [
      'token' => $request->user()->messaging_token,
      'notification' => [
        'title' => "Buy Product Seccess",
        'body' => 'Successfully buing product',
      ],
      'android' => [
        'notification' => [
          'priority' => "high",
          'icon' => 'stock_ticker_update',
          'sound' => "default",
          'color' => '#7e55c3',
          'imageUrl' => 'http://abdopr.ddns.net/file/currency-12',
        ],
        'data' => [
          'id' => "$product->id",
          'balance' => $request->total_price,
        ],
      ],
    ]);
    (new SocketClient($product->seller->id))->emitOrNotify('buy-seccess', [
      'token' => $product->seller->messaging_token,
      'notification' => [
        'title' => "Buy Product",
        'body' => 'Successfully buing product',
      ],
      'android' => [
        'notification' => [
          'priority' => "high",
          'icon' => 'stock_ticker_update',
          'sound' => "default",
          'color' => '#7e55c3',
          'imageUrl' => 'http://abdopr.ddns.net/file/currency-12',
        ],
        'data' => [
          'id' => "$product->id",
          'balance' => $request->total_price,
        ],
      ],
    ]);

    $this->apiSuccessResponse('Successfully buing product');
  }
}
