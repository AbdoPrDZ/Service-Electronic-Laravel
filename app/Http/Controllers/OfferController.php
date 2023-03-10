<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Setting;
use Illuminate\Http\Request;

class OfferController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all(Request $request) {
    if(!Setting::serviceIsActive('offers')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $items = Offer::whereIsDeleted('0')->get();
    $offers = [];
    foreach ($items as $offer) {
      $offers[$offer->id] = $offer;
    }
    return $this->apiSuccessResponse('Successfully getting offers', [
      'offers' => $offers,
    ]);
  }

}
