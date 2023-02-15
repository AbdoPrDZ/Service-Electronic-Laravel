<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Validator;

class PurchaseController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Purchase::all();
    $purchases = [];
    $purchases_repports = [];
    $waiting_purchases = [];
    foreach ($items as $value) {
      $value->linking();
      $purchases[$value->id] = $value;
      if(in_array($value->status, ['client_refuse','seller_reported'])) $purchases_repports[$value->id] = $value;
      if($value->satatus == 'waiting') $waiting_purchases[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => [
        'purchases' => $purchases,
        'purchases_repports' => $purchases_repports,
        'waiting_purchases' => $waiting_purchases,
      ],
    ]);
  }

  static function news($admin_id) {
    return count(Purchase::news($admin_id));
  }

  static function readNews($admin_id) {
    Purchase::readNews($admin_id);
  }

  public function answer(Request $request, $purchase) {
    $purchase->linking();
    $validator = Validator::make($request->all(), [
      'answer' => 'required|in:accept_delivery_cost,accept_all,refuse_all',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' =>$validator->errors()
      ]);
    }

    $steps = $purchase->delivery_steps;
    if(!in_array($purchase->status, ['client_refuse', 'seller_reported'])) {
      return $this->apiErrorResponse('You Already answerd');
    }

    if($request->answer == 'accept_all') {
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
    } else if($request->answer == 'refuse_all') {
      $res = $purchase->product_price_exchange->refuse('admin-refuse');
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->delivery_cost_exchange->refuse('admin-refuse');
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->commission_exchange->refuse('admin-refuse');
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
    } else if($request->answer == 'accept_delivery_cost') {
      $res = $purchase->delivery_cost_exchange->accept();
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->product_price_exchange->refuse('admin-refuse');
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
      $res = $purchase->commission_exchange->refuse('admin-refuse');
      if(!$res['success']) {
        return $this->apiErrorResponse($res['message']);
      }
    }
    $steps['admin_answer'] = [$request->answer, now()];
    $purchase->delivery_steps = $steps;
    $purchase->status = 'admin_answered';
    $purchase->unreades = Admin::unreades($request->user()->id);
    $purchase->unlinkingAndSave();

    return $this->apiSuccessResponse('Successfully ansowring');
  }

}
