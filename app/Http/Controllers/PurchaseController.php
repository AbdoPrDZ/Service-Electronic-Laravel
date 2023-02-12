<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Exchange;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Log;
use Validator;

class PurchaseController extends Controller {
  public function __construct() {
    $this->middleware('seller.access', [
      'except' => ['create', 'userAll', 'clientAnswer'],
    ]);
  }

  public function sellerAll(Request $request) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $user = $request->user();
    $user->linking();
    $purchases = $user->seller->purchases();
    return $this->apiSuccessResponse('Successfully getting data', [
      'purchases' => $purchases,
    ]);
  }

  public function userAll(Request $request) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

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

  static function encodeAddress(string $country, string $state, $address) {
    return "$country->$state".(!is_null($address) ? "->$address" : '');
  }

  static function decodeAddress(string $address) {
    list($country, $state, $address) = explode('->', $address);
    return [$country, $state, $address];
  }

  public function create(Request $request, Product $product) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

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

    $product->linking();
    $user = $request->user();
    $user->linking();

    if($product->seller->user_id == $user->id) {
      return $this->apiErrorResponse('You can\'t buy this product');
    }

    if(!key_exists($request->state, $product->seller->delivery_prices)) {
      return $this->apiErrorResponse('Your state is not supported', [
        'errors' => ['state' => 'Your state is not supported']
      ]);
    }

    $deliveryPrice = $product->seller->delivery_prices
      [$request->state]
      [$request->delivery_type];
    $totalPrice = $product->price * $request->count;
    $cmmissionPrice = ($product->price * $request->count * $product->commission);

    if (($totalPrice + $cmmissionPrice) > $user->wallet->balance) {
      return $this->apiErrorResponse('You don\'t have required balance to buy this product');
    }

    $purchaseId = Purchase::getNextSequenceValue();

    $deliveryCostExchange = Exchange::create([
      'name' => 'new-purchase-delivery-cost',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => $product->seller->user->wallet_id,
      'sended_balance' => $deliveryPrice,
      'received_balance' => $deliveryPrice,
    ]);
    $productPriceExchange = Exchange::create([
      'name' => 'new-purchase-product-price',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => $product->seller->user->wallet_id,
      'sended_balance' => $totalPrice,
      'received_balance' => $totalPrice,
    ]);
    $commissionExchange = Exchange::create([
      'name' => 'new-purchase-commission',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => Setting::platformCurrency()->platform_wallet_id,
      'sended_balance' => $cmmissionPrice,
      'received_balance' => $cmmissionPrice,
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
      'delivery_cost_exchange_id' => $deliveryCostExchange->id,
      'product_price_exchange_id' => $productPriceExchange->id,
      'commission_exchange_id' => $commissionExchange->id,
      'unreades' => Admin::unreades(),
    ]);

    return $this->apiSuccessResponse('Successfully buing product');
  }

  public function read(Request $request, Purchase $purchase) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $purchase->linking();

    $user = $request->user();

    if($purchase->product->seller->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $steps = $purchase->delivery_steps;
    $steps['seller_accept']['readed_at'] = now();
    $purchase->delivery_steps = $steps;
    $purchase->unlinkingAndSave();
    $purchase->linking();

    Notification::create([
      'name' => 'purchase-request-readed',
      'from_id' => $user->id,
      'from_model' => User::class,
      'to_id' => $purchase->user_id,
      'to_model' => User::class,
      'title' => 'Purchase request readed',
      'message' => 'Your purchase request has been readed',
      'data' => [
        'purchase_id' => $purchase->id,
      ],
      'image_id' => 'store',
      'type' => 'emit',
    ]);

    return $this->apiSuccessResponse('Successfully reading purchase');
  }

  public function sellerAnswer(Request $request, Purchase $purchase) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $purchase->linking();

    $user = $request->user();

    if ($purchase->product->seller->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $validator = Validator::make($request->all(), [
      'answer' => 'required|in:accept,refuse',
      'description' => $request->answer == 'refuse' ? 'required|string' : '',
    ]);

    if($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $steps = $purchase->delivery_steps;

    if(!is_null($steps['seller_accept']['answer'])) {
      return $this->apiErrorResponse('You are already answerd');
    }

    if ($request->answer == 'refuse') {
      $res = $purchase->delivery_cost_exchange->refuse($request->description);
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->product_price_exchange->refuse($request->description);
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->commission_exchange->refuse($request->description);
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
    }

    $steps['seller_accept']['answer'] = [$request->answer, $request->description, now()];
    $purchase->delivery_steps = $steps;
    $purchase->status = ['accept' => 'seller_accept', 'refuse' => 'seller_refuse'][$request->answer];
    $purchase->unreades = Admin::unreades($user->id);
    $purchase->unlinkingAndSave();

    Notification::create([
      'from_id' => $user->id,
      'from_model' => User::class,
      'to_id' => $purchase->user_id,
      'to_model' => User::class,
      'name' => 'purchase-seller-answer',
      'title' => [
        'accept' => 'Purchase request accepted',
        'refuse' => 'Purchase request refused',
      ][$request->answer],
      'message' => [
        'accept' => $request->description ?? 'Your request to purchase the product has been approved. You can track the product',
        'refuse' => $request->description,
      ][$request->answer],
      'data' => [
        'event_name' => 'purchase-seller-answer',
        'data' => json_encode([
          'purchase_id' => $purchase->id,
          'answer' => $request->answer,
          'description' => $request->description,
        ]),
      ],
      'image_id' => 'store',
      'type' => 'emitAndNotify',
    ]);
    Admin::notify([
      'name' => 'purchase-seller-answer',
      'title' => [
        'accept' => 'Purchase request accepted',
        'refuse' => 'Purchase request refused',
      ][$request->answer],
      'message' => [
        'accept' => $request->description ?? 'Your request to purchase the product has been approved. You can track the product',
        'refuse' => $request->description,
      ][$request->answer],
      'data' => [
        'purchase_id' => $purchase->id,
        'answer' => $request->answer,
        'description' => $request->description,
      ],
      'image_id' => 'store',
      'type' => 'emitAndNotify',
    ]);

    return $this->apiSuccessResponse('Successfully answer a purchase');
  }

  public function nextStep(Request $request, Purchase $purchase) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
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

    $steps['location_steps'][$nextStep] = now();
    $purchase->delivery_steps = $steps;
    if ($nextStep == 'delivering_to_client') {
      $purchase->status = 'waiting_client_answer';
    }
    $purchase->unreades = Admin::unreades();
    $purchase->unlinkingAndSave();
    $purchase->linking();

    Notification::create([
      'name' => 'purchase-step-updated',
      'from_id' => $user->id,
      'from_model' => User::class,
      'to_id' => $purchase->user_id,
      'to_model' => User::class,
      'title' => 'Purchase request deliery step updated',
      'message' => 'Your purchase request delivery has updated new step',
      'data' => [
        'purchase_id' => $purchase->id,
      ],
      'image_id' => 'store',
      'type' => 'emit',
    ]);

    return $this->apiSuccessResponse('Successfully changing step');
  }

  public function clientAnswer(Request $request, Purchase $purchase) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $purchase->linking();

    $user = $request->user();

    if($purchase->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $validator = Validator::make($request->all(), [
      'answer' => 'required|in:accept,refuse',
      'description' => $request->answer == 'refuse' ? 'required|string' : '',
    ]);

    if($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $steps = $purchase->delivery_steps;

    if (is_null($steps['receive'])) $steps['receive'] = [];
    if(key_exists('client', $steps['receive']) && !is_null($steps['receive']['client'])) {
      return $this->apiErrorResponse('You already answerd');
    }

    if($request->answer == 'accept') {
      $res = $purchase->product_price_exchange->accept();
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->delivery_cost_exchange->accept();
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->commission_exchange->accept();
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
    }

    $steps['receive']['client'] = [$request->answer, $request->description, now()];
    $purchase->delivery_steps = $steps;
    $purchase->status = ['accept' => 'client_accept', 'refuse' => 'client_refuse'][$request->answer];
    $purchase->unreades = Admin::unreades();
    $purchase->unlinkingAndSave();
    $purchase->linking();

    $answer = ['refuse' => 'refused', 'accept' => 'accepted'][$request->answer];
    Notification::create([
      'from_id' => $user->id,
      'from_model' => User::class,
      'to_id' => $purchase->product->seller->user_id,
      'to_model' => User::class,
      'name' => 'purchase-client-answer',
      'title' => "Your delivery has been $answer",
      'message' => "A client $answer your delivery",
      'data' => [
        'event_name' => 'purchase-client-answer',
        'data' => json_encode([
          'purchase_id' => $purchase->id,
          'answer' => $request->answer,
          'description' => $request->description,
        ]),
        'image_id' => 'store',
        'type' => 'emitAndNotify',
      ]
    ]);

    Admin::notify([
      'name' => 'purchase-client-answer',
      'title' => [
        'accept' => 'Purchase delivery accepted',
        'refuse' => 'Purchase delivery refused',
      ][$request->answer],
      'message' => [
        'accept' => 'A client accepted his delivery.',
        'refuse' => "A client refused his delivery becuase ($request->description)",
      ][$request->answer],
      'data' => [
        'purchase_id' => $purchase->id,
        'answer' => $request->answer,
        'description' => $request->description,
      ],
      'image_id' => 'store',
      'type' => 'emitAndNotify',
    ]);

    return $this->apiSuccessResponse('Successfully answering');
  }

  public function sellerReport(Request $request, Purchase $purchase) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $purchase->linking();

    $user = $request->user();

    if ($purchase->product->seller->user->id != $user->id) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $validator = Validator::make($request->all(), [
      'report' => 'required|string',
    ]);

    if($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    if(!in_array($purchase->status, ['waiting_client_answer', 'client_refuse'])) {
      return $this->apiErrorResponse('Unauthenticated');
    }

    $steps = $purchase->delivery_steps;

    if (is_null($steps['receive']))$steps['receive'] = [];
    if(key_exists('seller', $steps['receive']) &&!is_null($steps['receive']['seller'])) {
      return $this->apiErrorResponse('You already repported');
    }

    $time = $purchase->status == 'waiting_client_answer' ? $steps['location_steps']['delivering_to_client'] : $steps['receive']['client'][0];
    if(now()->diffInDays($time) < 1) {
      return $this->apiErrorResponse('Please wait until the waiting time has expired (24h)');
    }

    $steps['receive']['seller'] = ['seller_report', $request->report, now()];
    $purchase->delivery_steps = $steps;
    $purchase->status = 'seller_reported';
    $purchase->unlinkingAndSave();

    Notification::create([
      'from_id' => $user->id,
      'from_model' => User::class,
      'to_id' => $purchase->user_id,
      'to_model' => User::class,
      'name' => 'purchase-seller-repport',
      'title' => "A seller sended repport",
      'message' => "A seller sended repport for your purchase request",
      'data' => [
        'event_name' => 'purchase-seller-repport',
        'data' => json_encode([
          'purchase_id' => $purchase->id,
          'answer' => $request->answer,
          'description' => $request->description,
        ]),
        'image_id' => 'store',
        'type' => 'emitAndNotify',
      ]
    ]);

    Admin::notify([
      'name' => 'purchase-seller-repport',
      'title' => 'Purchase request repported',
      'message' => 'A seller sended his repport for purchase repport',
      'data' => [
        'purchase_id' => $purchase->id,
        'answer' => $request->answer,
        'description' => $request->description,
      ],
      'image_id' => 'store',
      'type' => 'emitAndNotify',
    ]);

    return $this->apiSuccessResponse('Successfully reporting');
  }

}
