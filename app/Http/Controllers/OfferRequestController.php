<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Exchange;
use App\Models\OfferRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Validator;

class OfferRequestController extends Controller {

  public function all(Request $request) {
    if(!Setting::serviceIsActive('offers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $items = OfferRequest::where('user_id', '=', $request->user()->id)->get();
    $offerRequests = [];
    foreach ($items as $offer) {
      $offer->linking();
      $offerRequests[$offer->id] = $offer;
    }
    return $this->apiSuccessResponse('Successfully getting offers', [
      'offer_requests' => $offerRequests,
    ]);
  }

  public function create(Request $request, $offer) {
    $validates = [];
    foreach ($offer->fields as $name => $field) {
      $validates[$name] = $field['validate'];
    }
    $validates['sub_offer'] = 'required|in:' . implode(',', array_keys($offer->sub_offers));

    $validator = Validator::make($request->all(), $validates);
    if($validator->fails()){
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    if(!Setting::serviceIsActive('offers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $fieldsData = [];
    foreach ($offer->fields as $name => $field) {
      $fieldsData[$name] = $request->{$name};
    }
    $totalPrice = $offer->sub_offers[$request->sub_offer]['price'];

    $user = $request->user();
    $user->linking();

    if($totalPrice> $user->wallet->balance) {
      return $this->apiErrorResponse('You don\'t have required balance');
    }

    $exchange = Exchange::create([
      'name' => 'buy-service',
      'from_wallet_id' => $user->wallet_id,
      'to_wallet_id' => Setting::platformCurrency()->platform_wallet_id,
      'sended_balance' => $totalPrice,
      'received_balance' => $totalPrice,
    ]);

    $offerRequest = OfferRequest::create([
      'user_id' => $request->user()->id,
      'offer_id' => $offer->id,
      'sub_offer' => $request->sub_offer,
      'fields' => $fieldsData,
      'total_price' => $totalPrice,
      'unreades' => Admin::unreades(),
      'exchange_id' => $exchange->id,
    ]);

    return $this->apiSuccessResponse('Successfully requesting the offer', ['offerRequest' => $offerRequest]);
  }
}
