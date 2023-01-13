<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function all(Request $request) {
    $items = Offer::where('is_deleted', '=', 0)->get();
    $offers = [];
    foreach ($items as $offer) {
      $offers[$offer->id] = $offer;
    }
    return $this->apiSuccessResponse('Successfully getting offers', [
      'offers' => $offers,
    ]);
  }

}
