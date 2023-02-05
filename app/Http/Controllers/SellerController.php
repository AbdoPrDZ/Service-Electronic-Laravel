<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\File;
use App\Models\Seller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class SellerController extends Controller {

  public function register(Request $request) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $validator = Validator::make($request->all(), [
      'store_name' => 'required|string',
      'store_address' => 'required|string',
      'store_image' => 'file|mimes:jpg,png,jpeg',
      'delivery_prices' => 'required'
    ]);
    if($validator->fails()){
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }
    try {
      $delivery_prices = json_decode($request->delivery_prices);
    } catch (\Throwable $th) {
      $this->apiErrorResponse('invalid delivery prices', [
        'errors' => ['prices' => 'Invalid delivery prices']
      ]);
    }

    if($request->user()->identity_verifited_at == null) {
      return $this->apiErrorResponse('You identity not verifited');
    }

    $seller = Seller::where('user_id', '=', $request->user()->id)->first();
    if(!is_null($seller) && $seller->status != 'refused') {
      return $this->apiErrorResponse('You allready a seller');
    }

    $sellerId = $seller?->id ?? Seller::GetNextSequenceValue();
    $values = [
      'id' => $sellerId,
      'user_id' => $request->user()->id,
      'store_name' => $request->store_name,
      'store_address' => $request->store_address,
      'delivery_prices' => $delivery_prices,
      'unreades' => Admin::unreades(),
    ];

    if(!is_null($request->file('store_image'))) {
      if(!Storage::disk('api')->exists('sellers_data')) {
        Storage::disk('api')->makeDirectory('sellers_data');
      }
      $sellerFilesPath = "sellers_data/$sellerId";
      if(!Storage::disk('api')->exists($sellerFilesPath)) {
        Storage::disk('api')->makeDirectory($sellerFilesPath);
      }
      $allpath = Storage::disk('api')->path("sellers_data/$sellerId");
      $time = now()->timestamp;
      $shortPath = "$sellerFilesPath/si-$time";
      $request->file('store_image')->move($allpath, "si-$time");
      File::create([
        'name' => "s-$sellerId-si-$time",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $values['store_image_id'] = "s-$sellerId-si-$time";
    }

    if (!is_null($seller)){
      $seller->answered_at = null;
      $seller->status = 'checking';
      $seller->store_name = $request->store_name;
      $seller->store_address = $request->store_address;
      $seller->delivery_prices = $delivery_prices;
      $seller->save();
    } else {
      $seller = Seller::create($values);
    }

    return $this->apiSuccessResponse('Successfully creating store, you can start publish your products');
  }

  public function edit(Request $request) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $validator = Validator::make($request->all(), [
      'store_name' => 'string',
      'store_address' => 'string',
      'store_image' => 'file|mimes:jpg,png,jpeg',
    ]);
    if($validator->fails()){
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    if($request->user()->identity_verifited_at == null) {
      return $this->apiErrorResponse('You identity not verifited');
    }

    $seller = Seller::where('user_id', '=', $request->user()->id)->first();
    if(is_null($seller)) {
      return $this->apiErrorResponse('You are not a seller');
    }

    $seller->store_address = $request->store_address ?? $seller->store_address;
    if(!is_null($request->file('store_image'))) {
      File::find($seller->store_image_id)?->delete();
      if(!Storage::disk('api')->exists('sellers_data')) {
        Storage::disk('api')->makeDirectory('sellers_data');
      }
      $sellerFilesPath = "sellers_data/$seller->id";
      if(!Storage::disk('api')->exists($sellerFilesPath)) {
        Storage::disk('api')->makeDirectory($sellerFilesPath);
      }
      $allpath = Storage::disk('api')->path("sellers_data/$seller->id");
      $time = now()->timestamp;
      $shortPath = "$sellerFilesPath/si-$time";
      $request->file('store_image')->move($allpath, "si-$time");
      $imageFile = File::create([
        'name' => "s-$seller->id-si-$time",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $seller->store_image_id = $imageFile->name;
    }
    $seller->save();

    $this->apiSuccessResponse('Successfully updating settings');

  }
}
