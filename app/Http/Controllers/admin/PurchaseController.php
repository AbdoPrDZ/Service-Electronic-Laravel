<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class PurchaseController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Purchase::all();
    $purchases = [];
    foreach ($items as $value) {
      $value->linking();
      $purchases[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => $purchases,
    ]);
  }

  static function news($admin_id) {
    return count(Purchase::news($admin_id));
  }

  static function readNews($admin_id) {
    Purchase::readNews($admin_id);
    return Controller::apiSuccessResponse('successfully reading news');
  }

  public function ansower(Request $request, $purchase) {
    $purchase->linking();
    $validator = Validator::make($request->all(), [
      'ansower' => 'required|in:accept_delivery_cost,accept_all,refuse_all',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }

    $steps = $purchase->delivery_steps;
    if(!in_array($purchase->status, ['client_refuse', 'seller_reported'])) {
      return $this->apiErrorResponse('You Already ansowerd');
    }

    if($request->ansower == 'accept_all') {
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
    } else if($request->ansower == 'refuse_all') {
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
    } else if($request->ansower == 'accept_delivery_cost') {
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
    $steps['admin_ansower'] = [$request->ansower, Carbon::now()];
    $purchase->delivery_steps = $steps;
    $purchase->status = 'admin_ansowred';
    $purchase->unreades = Admin::unreades($request->user()->id);
    $purchase->unlinkingAndSave();

    return $this->apiSuccessResponse('Successfully ansowring');
  }

}
