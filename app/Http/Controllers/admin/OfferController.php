<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\File;
use App\Models\Offer;
use App\Models\OfferRequest;
use Illuminate\Http\Request;
use Storage;
use Validator;

class OfferController extends Controller {

  static function all(Request $request) {
    $items = Offer::where('is_deleted', '=', 0)->get();
    $offers = [];
    foreach ($items as $item) {
      $offers[$item->id] = $item;
    }
    $items = OfferRequest::all();
    $offerRequests = [];
    foreach ($items as $item) {
      $item->linking();
      $offerRequests[$item->id] = $item;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => [
        'offers' => $offers,
        'offer_requests' => $offerRequests,
      ]
    ]);
  }

  static function news($admin_id) {
    return count(Offer::news($admin_id)) + count(OfferRequest::news($admin_id));
  }

  static function readNews($admin_id) {
    Offer::readNews($admin_id);
    OfferRequest::readNews($admin_id);
  }

  public function create(Request $request) {
    $request->merge(['sub_offers' => $this->tryDecodeArray($request->sub_offers)]);
    $request->merge(['fields' => $this->tryDecodeArray($request->fields)]);
    $request->merge(['data' => $this->tryDecodeArray($request->data)]);
    $validator = Validator::make($request->all(), [
      'title_en' => 'required|string',
      'title_ar' => 'required|string',
      'image' => 'required|file|mimes:jpg,png,jpeg',
      'description_en' => 'required|string',
      'description_ar' => 'required|string',
      'sub_offers' => 'required|array',
      'fields' => 'array',
      'data' => 'array',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' =>$validator->errors(),
      ]);
    }

    $offerId = Offer::getNextSequenceValue();

    if(!Storage::disk('public')->exists("offers")) {
      Storage::disk('public')->makeDirectory("offers");
    }
    $time = now()->timestamp;
    $request->file('image')->move(Storage::disk('public')->path("offers"), "$offerId-$time");
    $imageFile = File::create([
      'name' => "offer-$offerId-$time",
      'disk' => 'public',
      'type' => 'image',
      'path' => "offers/$offerId-$time",
    ]);

    Offer::create([
      'id' => $offerId,
      'image_id' => $imageFile->name,
      'title' => [
        'en' =>$request->title_en,
        'ar' =>$request->title_ar,
      ],
      'description' => [
        'en' => $request->description_en,
        'ar' => $request->description_ar,
      ],
      'sub_offers' => $this->listMap2Map($request->sub_offers),
      'fields' => $this->listMap2Map($request->fields),
      'data' => $this->listMap2Map($request->data),
      'unreades' => Admin::unreades($request->user()->id)
    ]);

    return $this->apiSuccessResponse('Successfully creating offer');
  }

  public function edit(Request $request, Offer $offer) {
    $request->merge(['sub_offers' => $this->tryDecodeArray($request->sub_offers)]);
    $request->merge(['fields' => $this->tryDecodeArray($request->fields)]);
    $request->merge(['data' => $this->tryDecodeArray($request->data)]);
    $validator = Validator::make($request->all(), [
      'title_en' => 'string',
      'title_ar' => 'string',
      'image' => 'file|mimes:jpg,png,jpeg',
      'description_en' => 'string',
      'description_ar' => 'string',
      'sub_offers' => 'array',
      'fields' => 'array',
      'data' => 'array',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' =>$validator->errors(),
      ]);
    }

    if($request->image) {
      if(!Storage::disk('public')->exists("offers")) {
        Storage::disk('public')->makeDirectory("offers");
      }
      $time = now()->timestamp;
      $request->file('image')->move(Storage::disk('public')->path("offers"), "$offer->id-$time");
      $imageFile = File::create([
        'name' => "offer-$offer->id-$time",
        'disk' => 'public',
        'type' => 'image',
        'path' => "offers/$offer->id-$time",
      ]);
      $offer->image_id = $imageFile->name;
    }

    $title = $offer->title;
    if($request->title_en) $title['en'] = $request->title_en;
    if($request->title_ar) $title['ar'] = $request->title_ar;
    $description = $offer->description;
    if($request->description_en) $description['en'] = $request->description_en;
    if($request->description_ar) $description['ar'] = $request->description_ar;

    $offer->title = $title;
    $offer->description = $description;
    if($request->sub_offers) $offer->sub_offers = $this->listMap2Map($request->sub_offers);
    if($request->fields) $offer->fields = $this->listMap2Map($request->fields);
    if($request->data) $offer->data = $this->listMap2Map($request->data);
    $offer->unreades = Admin::unreades($request->user()->id);
    $offer->save();

    return $this->apiSuccessResponse('Successfully creating offer');
  }

  public function delete(Request $request, $offer) {
    // File::find($offer->image_id)->delete();
    $offer->preDelete();
    return $this->apiSuccessResponse('Successfully deleteing offer');
  }

}
