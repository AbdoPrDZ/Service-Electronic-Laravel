<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Validator;

class OfferRequestController extends Controller {

  public function answer(Request $request, $offerRequest) {
    $offerRequest->linking();

    $validates = [
      'answer' => 'required|in:accept,refuse',
    ];
    if($request->answer == 'accept') {
      foreach ($offerRequest->offer->data as $name => $item) {
        $validates[$name] = $item['validate'];
      }
    } else if($request->answer == 'refuse') {
      $validates['description'] = 'required|string';
    }
    $validator = Validator::make($request->all(), $validates);
    if($validator->fails()){
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
        'all' => $request->all(),
      ]);
    }

    if($offerRequest->status != 'waiting_admin_accept') {
      return $this->apiErrorResponse('You Already answer this request');
    }

    if($request->answer == 'accept') {
      $data = [];
      foreach ($offerRequest->offer->data as $name => $item) {
        $data[$name] = $request->get($name);
      }
      $offerRequest->data = $data;
      $offerRequest->status = 'admin_accept';
      $offerRequest->exchange->accept();
    } else {
      $offerRequest->exchange->refuse($request->description);
      $offerRequest->status = 'admin_refuse';
    }
    $offerRequest->unreades = Admin::unreades($request->user()->id);
    $offerRequest->unlinkingAndSave();

    return $this->apiSuccessResponse('Successfully ansowring request');
  }

  public function delete(Request $request, $offerRequest) {
    $offerRequest->linking();

    $offerRequest->exchange->refuse();
    $offerRequest->exchange->delete();
    $offerRequest->delete();

    return $this->apiSuccessResponse('Successfully deleting request');
  }

}
