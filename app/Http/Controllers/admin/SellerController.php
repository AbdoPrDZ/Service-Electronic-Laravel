<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class SellerController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = Seller::all();
    $sellers = [];
    $newSellers = [];
    foreach ($items as $seller) {
      $seller->linking();
      $sellers[$seller->id] = $seller;
      if($seller->status == 'checking') $newSellers[$seller->id] = $seller;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => [
        'sellers' => $sellers,
        'new_sellers' => $newSellers,
      ]
    ]);
  }

  static function news($admin_id) {
    $newsSellers = Seller::news($admin_id);
    $newsNewSellers = array_filter($newsSellers, function ($seller) {
      return $seller->status == 'checking';
    });
    return count($newsSellers) + count($newsNewSellers);
  }

  static function readNews($admin_id) {
    Seller::readNews($admin_id);
  }

  public function changeStatus(Request $request, Seller $seller) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|string',
      'description' => '',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' =>$validator->errors()
      ]);
    }
    if(in_array($request->status, ['accepted', 'refused'])) {
      if($request->status == 'refused' && $request->description) {
        return $this->apiErrorResponse('description required');
      }
      if($request->status == 'accepted' && $seller->answered_at != null) {
        return $this->apiErrorResponse('This Seller already answered');
      }
      $seller->status = $request->status;
      $seller->anower_description = $request->description;
      $seller->answered_at = now();
      $seller->unreades = Admin::unreades($request->user()->id);
      $seller->unlinkingAndSave();
      $messages = [ 'accepted' => 'Congratulations, your request has been accepted' ];
      Notification::create([
        'from_id' => $request->user()->id,
        'from_model' => Admin::class,
        'to_id' => $seller->user_id,
        'to_model' => User::class,
        'name' => 'seller-register-status-change',
        'title' => 'Seller register result',
        'message' => $request->description ?? $messages[$request->status] ?? '',
        'data' => [
          'event_name' => 'seller-register-status-change',
          'data' => json_encode([
            'status' => $request->status,
          ]),
        ],
        'image_id' => 'store',
        'type' => 'emitOrNotify',
      ]);
      return $this->apiSuccessResponse('Successfully changing status');
    } else {
      return $this->apiErrorResponse('Invalid status');
    }
  }
}
