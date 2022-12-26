<?php

namespace App\Http\Controllers;

use App\Http\SocketBridge\SocketClient;
use App\Models\Admin;
use App\Models\Exchange;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Seller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class PurchaseController extends Controller {
  public function __construct() {
    $this->middleware('seller.access', [
      'except' => ['create', 'userAll'],
    ]);
  }

  public function sellerAll(Request $request) {
    $user = $request->user();
    $user->linking();
    $purchases = [];
    $sellerProducts = Product::where('seller_id', '=', $user->seller_id)->get();
    foreach ($sellerProducts as $product) {
      $purchase = Purchase::where('product_id', '=', $product->id)->first();
      if($purchase) {
        $purchase->linking();
        $purchases[$purchase->id] = $purchase;
      }
    }
    return $this->apiSuccessResponse('Successfully getting data', [
      'purchases' => $purchases,
    ]);
  }

  public function userAll(Request $request) {
    $items = Purchase::where('user_id', '=',  $request->user()->id)->get();
    $purchases = [];
    foreach ($items as $item) {
      $item->linking();
      $purchases[$item->id] = $item;
    }
    return $this->apiSuccessResponse('Successfully getting data', [
      'purchases' => $purchases,
    ]);
  }

  public function getNextId() {
    $id = 0;
    $last = Purchase::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  static function encodeAddress(string $country, string $state, $address) {
    return "$country->$state".(!is_null($address) ? "->$address" : '');
  }

  static function decodeAddress(string $address) {
    list($country, $state, $address) = explode('->', $address);
    return [$country, $state, $address];
  }

  public function create(Request $request, $product_id) {
    $validator = Validator::make($request->all(), [
      'fullname' => 'required|string',
      'count' => 'required|integer',
      'phone' => 'required|string',
      'state' => 'required|string',
      'address' => $request->delivery_type == 'home' ? 'required|string' : '',
      'delivery_type' => 'required|in:home,office',
    ]);
    if($validator->fails()){
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $product = Product::find($product_id);
    if(is_null($product)) {
      return $this->apiErrorResponse('Invalid product Id');
    }
    $product->linking();
    $user = $request->user();
    $user->linking();

    if($product->seller->user_id == $user->id) {
      return $this->apiErrorResponse('You can\'t buy this product');
    }

    if(!key_exists($request->state, $product->seller->delivery_prices)) {
      return $this->apiErrorResponse('Your state is not supported', [$request->state, $product->seller->delivery_prices]);
    }

    $deliveryPrice = $product->seller->delivery_prices
      [$request->state]
      [$request->delivery_type];
    $totalPrice = ($product->price * $request->count) + ($product->price * $request->count * $product->commission);

    if ($totalPrice > $user->wallet->balance) {
      return $this->apiErrorResponse('You don\'t have required balance to buy this product');
    }

    $purchaseId = $this->getNextId();

    $deliveryCostExchane = Exchange::create([
      'name' => 'new-purchase-delivery-cost',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => $product->seller->user->wallet_id,
      'sended_balance' => $deliveryPrice,
      'received_balance' => $deliveryPrice,
    ]);

    $exchange = Exchange::create([
      'name' => 'new-purchase',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => $product->seller->user->wallet_id,
      'sended_balance' => $totalPrice,
      'received_balance' => $totalPrice,
    ]);
    Purchase::create([
      'id' => $purchaseId,
      'user_id' => $user->id,
      'fullname' => $request->fullname,
      'product_id' => $product->id,
      'count' => $request->count,
      'phone' => $request->phone,
      'address' => $this->encodeAddress('Algeria', $request->state, $request->address),
      'delivery_type' => $request->delivery_type,
      'total_price' => $totalPrice + $deliveryPrice,
      'delivery_cost_exchange_id' => $deliveryCostExchane->id,
      'product_price_exchange_id' => $exchange->id,
      'unreades' => Admin::unreades(),
      // 'delivery_steps' => [
        // 'seller_accept' => [
        //   'readed_at' => '2022-12-24 10:28',
        //   'ansower' => ['accept', 'discription', '2022-12-24 11:28'],
        // ],
        // 'location_steps' => [
        //   'charging' => '2022-12-24 13:20',
        //   'out_from_state' => '2022-12-24 14:10',
        //   'in_to_state' => '2022-12-24 20:15',
        //   'discharging_on_office' => '2022-12-24 20:30',
        //   'delivering_to_client' => '2022-12-25 08:07',
        // ],
        // 'receive' => [
        //   'received' => ['received', 'discription', '2022-12-25 08:10'],
          // 'received' => ['refused', 'discription', '2022-12-25 08:10'],
          // 'received' => ['absent', 'discription', '2022-12-25 08:10'],
        // ],
      // ]
    ]);

    Notification::create([
      'to_id' => $product->seller->user_id,
      'to_model' => User::class,
      'name' => 'notifications',
      'title' => 'A new product has been sold',
      'message' => 'New Product solded by User(' . $user->fullname . ')',
      'data' => [
        'event_name' => 'new-product-solded',
        'data' => json_encode([
          'product_id' => $product->id,
          'count' => $request->count,
          'purchase_id' => $purchaseId,
        ]),
      ],
      'image_id' => 'currency-4',
      'type' => 'emitAndNotify',
    ]);

    return $this->apiSuccessResponse('Successfully buing product');
  }

  public function read(Request $request, $id) {
    $purchase = Purchase::find($id);
    if(is_null($purchase)) {
      return $this->apiErrorResponse('Invalid id', [], 404);
    }
    $purchase->linking();

    $user = $request->user();

    if($purchase->product->seller->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $steps = $purchase->delivery_steps;
    $steps['seller_accept']['readed_at'] = Carbon::now();
    $purchase->delivery_steps = $steps;
    $purchase->unlinkingAndSave();

    return $this->apiSuccessResponse('Successfully reading purchase');
  }

  public function ansower(Request $request, $id) {
    $purchase = Purchase::find($id);
    if (is_null($purchase)) {
      return $this->apiErrorResponse('Invalid id', [], 404);
    }
    $purchase->linking();

    $user = $request->user();

    if ($purchase->product->seller->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $validator = Validator::make($request->all(), [
      'ansower' => 'required|in:accept,refuse',
      'discription' => $request->ansower == 'refuse' ? 'required|string' : '',
    ]);

    if($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $steps = $purchase->delivery_steps;

    if(!is_null($steps['seller_accept']['ansower'])) {
      return $this->apiErrorResponse('You are already ansowerd');
    }

    if ($request->ansower == 'refuse'){
      $purchase->delivery_cost_exchange->refuse($request->discription);
      $purchase->product_price_exchange->refuse($request->discription);
    }

    $steps['seller_accept']['ansower'] = [$request->ansower, $request->discription, Carbon::now()];
    $purchase->delivery_steps = $steps;
    $purchase->status = ['accept' => 'seller_accept', 'refuse' => 'seller_refuse'][$request->ansower];
    $purchase->unreades = Admin::unreades($user->id);
    $purchase->unlinkingAndSave();

    Notification::create([
      'from_id' => $user->id,
      'from_model' => User::class,
      'to_id' => $purchase->user_id,
      'to_model' => User::class,
      'name' => 'notifications',
      'title' => [
        'accept' => 'Purchase request accepted',
        'refuse' => 'Purchase request refused',
      ][$request->ansower],
      'message' => [
        'accept' => $request->discription ?? 'Your request to purchase the product has been approved. You can track the product',
        'refuse' => $request->discription,
      ][$request->ansower],
      'data' => [
        'event_name' => 'purchase-seller-ansower',
        'data' => json_encode([
          'purchase_id' => $purchase->id,
          'ansower' => $request->ansower,
          'discription' => $request->discription,
        ]),
      ],
      'image_id' => 'currency-4',
      'type' => 'emitAndNotify',
    ]);

    return $this->apiSuccessResponse('Successfully ansower a purchase');
  }

  public function nextStep(Request $request, $id) {
    $purchase = Purchase::find($id);
    if (is_null($purchase)) {
      return $this->apiErrorResponse('Invalid id', [], 404);
    }
    $purchase->linking();

    $user = $request->user();

    if ($purchase->product->seller->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $steps = $purchase->delivery_steps;
    if (is_null($steps['location_steps'])) $steps['location_steps'] = [];
    $nextStep = null;
    foreach (['charging', 'out_from_state', 'in_to_state', 'discharging_on_office', 'delivering_to_client'] as $step) {
      if (is_null($steps['location_steps'][$step])) {
        $nextStep = $step;
        break;
      }
    }

    if(is_null($nextStep)) return $this->apiErrorResponse('All steps done');

    $steps['location_steps'][$nextStep] = Carbon::now();
    $purchase->delivery_steps = $steps;
    $purchase->unreades = Admin::unreades();
    $purchase->unlinkingAndSave();

    return $this->apiSuccessResponse('Successfully changing step');
  }

  public function clientAnsower(Request $request, $id) {
    $purchase = Purchase::find($id);
    if (is_null($purchase)) {
      return $this->apiErrorResponse('Invalid id', [], 404);
    }
    $purchase->linking();

    $user = $request->user();

    if($purchase->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $validator = Validator::make($request->all(), [
      'ansower' => 'required|in:accept,refuse',
      'discription' => $request->ansower == 'refuse' ? 'required|string' : '',
    ]);

    if($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $steps = $purchase->delivery_steps;

    if(!is_null($steps['receive'])) {
      return $this->apiErrorResponse('You already ansowerd');
    }

    if($request->ansower == 'accept') {
      $purchase->product_price_exchange->accept();
      $purchase->delivery_cost_exchange->accept();
    } else {
      $purchase->product_price_exchange->refuse($request->discription);
      $purchase->delivery_cost_exchange->refuse($request->discription);
    }

    $steps['receive'] = [$request->ansower, $request->discription, Carbon::now()];
    $purchase->delivery_steps = $steps;
    $purchase->status = ['accept' => 'client_accept', 'refuse' => 'client_refuse'][$request->ansower];
    $purchase->unreades = Admin::unreades();
    $purchase->unlinkingAndSave();

    return $this->apiSuccessResponse('Successfully ansowering');
  }

  public function clientAbsent(Request $request, $id) {
    $purchase = Purchase::find($id);
    if (is_null($purchase)) {
      return $this->apiErrorResponse('Invalid id', [], 404);
    }
    $purchase->linking();

    $user = $request->user();

    if ($purchase->product->seller->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $validator = Validator::make($request->all(), [
      'discription' => 'required|string',
    ]);

    if($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $steps = $purchase->delivery_steps;

    if(!is_null($steps['receive'])) {
      return $this->apiErrorResponse('This purchase already ansowerd');
    }

    $purchase->product_price_exchange->refuse($request->discription);
    $purchase->delivery_cost_exchange->refuse($request->discription);
    $steps['receive'] = ['absent', $request->discription, Carbon::now()];
    $purchase->delivery_steps = $steps;
    $purchase->unreades = Admin::unreades($user->id);
    $purchase->unlinkingAndSave();

    return $this->apiSuccessResponse('Successfully ansowering');
  }

}
