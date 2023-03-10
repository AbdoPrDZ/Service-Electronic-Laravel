<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Seller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\File;
use Validator;

class ProductController extends Controller {

  public function all(Request $request) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $query = $request->query();
    $items = Product::whereIsDeleted('0')->get();
    $from = key_exists('from', $query) && intVal($query['from']) < count($items) && intVal($query['from']) >= 0 ? intVal($query['from']) : 0;
    $to = key_exists('to', $query) && intVal($query['to']) <= count($items)&& intVal($query['to']) > $from ? intVal($query['to']) : count($items);
    $products = [];
    for ($i = $from; $i < $to; $i++) {
      $product = $items[$i];
      $product->linking($request->user());
      $products[$product->id] = $product;
    }
    return $this->apiSuccessResponse('Successfully getting products', [
      'products' => $products,
    ]);
  }

  public function create(Request $request) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'description' => 'required|string',
      'price' => 'required|numeric',
      'count' => 'required|integer',
      'category_id' => 'required|integer',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = $request->user();

    $seller = Seller::whereUserId($user->id)->first();
    if(is_null($seller)) {
      return $this->apiErrorResponse('You are not seller');
    }
    if($user->identity_verifited_at == null) {
      return $this->apiErrorResponse('You identity not verifited');
    }
    if(is_null(Category::find($request->category_id))) {
      return $this->apiErrorResponse('Invalid category', [
        'errors' => ['category' => 'Invalid category']
      ]);
    }

    $commission = Setting::commission();

    $productId = Product::getNextSequenceValue();
    $values = [
      'id' => $productId,
      'seller_id' => $seller->id,
      'name' => $request->name,
      'description' => $request->description,
      'price' => $request->price,
      'commission' => $commission,
      'count' => $request->count,
      'category_id' => $request->category_id,
      'images_ids' => [],
      'unreades' => Admin::unreades(),
    ];

    if(!Storage::disk('api')->exists('users_data')) {
      Storage::disk('api')->makeDirectory('users_data');
    }
    $userFilesPath = "users_data/".$user->id;
    if(!Storage::disk('api')->exists($userFilesPath)) {
      Storage::disk('api')->makeDirectory($userFilesPath);
      Storage::disk('api')->makeDirectory("$userFilesPath/products");
    }
    $userProductPath = "$userFilesPath/products/$productId";
    if(!Storage::disk('api')->exists($userProductPath)) {
      Storage::disk('api')->makeDirectory($userProductPath);
    }

    $i = 1;
    $time = now()->timestamp;
    foreach ($request->files as $file) {
      $file->move(Storage::disk('api')->path($userProductPath), "i-$i-$time");
      $name = "u-" . $user->id . "-p-$productId-i-$i-$time";
      File::create([
        'name' => $name,
        'disk' => 'api',
        'type' => 'image',
        'path' => "$userProductPath/i-$i-$time",
      ]);
      $values['images_ids'][] = $name;
      $i++;
    }

    Product::create($values);

    return response()->json([
      'success' => true,
      'message' => 'Successfully creating product',
    ]);

  }

  public function edit(Request $request, Product $product) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $validator = Validator::make($request->all(), [
      'name' => 'string',
      'description' => 'string',
      'price' => 'numeric',
      'count' => 'integer',
      'category_id' => 'integer',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $seller = Seller::whereUserId($request->user()->id)->first();
    if(is_null($seller)) {
      return $this->apiErrorResponse('You are not seller');
    }
    if($request->user()->identity_verifited_at == null) {
      return $this->apiErrorResponse('You identity not verifited');
    }

    if($product->seller_id != $seller->id) {
      return $this->apiErrorResponse('Bad request.', [], 400);
    }

    if(count($request->files) > 0) {
      $user = $request->user();
      $imagesIds = [];
      foreach ($product->images_ids as $imageId) {
        File::find($imageId)?->delete();
      }
      if(!Storage::disk('api')->exists('users_data')) {
        Storage::disk('api')->makeDirectory('users_data');
      }
      $userFilesPath = "users_data/".$user->id;
      if(!Storage::disk('api')->exists($userFilesPath)) {
        Storage::disk('api')->makeDirectory($userFilesPath);
        Storage::disk('api')->makeDirectory("$userFilesPath/products");
      }
      $userProductPath = "$userFilesPath/products/$product->id";
      if(!Storage::disk('api')->exists($userProductPath)) {
        Storage::disk('api')->makeDirectory($userProductPath);
      }

      async(function () use ($product) {
        foreach ($product->images_ids as $id) {
          File::find($id)?->delete();
        }
      })->start();

      $i = 1;
      $time = now()->timestamp;
      foreach ($request->files as $file) {
        $file->move(Storage::disk('api')->path($userProductPath), "i-$i-$time");
        $name = "u-" . $user->id . "-p-$product->id-i-$i-$time";
        File::create([
          'name' => $name,
          'disk' => 'api',
          'type' => 'image',
          'path' => "$userProductPath/i-$i-$time",
        ]);
        $imagesIds[] = $name;
        $i++;
      }
      $product->images_ids = $imagesIds;
    }

    $product->name = $request->name ?? $product->name;
    $product->description = $request->description ?? $product->description;
    $product->price = $request->price ?? $product->price;
    $product->count = $request->count ?? $product->count;
    $product->category_id = $request->category_id ?? $product->category_id;
    $product->save();
    return response()->json([
      'success' => true,
      'message' => 'Successfully editing product'
    ]);
  }

  public function delete(Request $request, Product $product) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $product->preDelete();
    return $this->apiSuccessResponse('Successfully deleting product');
  }

  public function like(Request $request, $product) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $user = $request->user();
    $product->linking($user);
    if($product->is_liked) {
      return $this->apiErrorResponse('You already liked this product');
    }
    $product->likes = [...$product->likes, $user->id];
    $product->unlinkingAndSave();
    return $this->apiSuccessResponse('Successfully liking product');
  }

  public function unLike(Request $request, $product) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $user = $request->user();
    $product->linking($user);
    if(!$product->is_liked) {
      return $this->apiErrorResponse('You are not liked this product');
    }
    $product->likes = array_diff($product->likes, [$user->id]);
    $product->unlinkingAndSave();
    return $this->apiSuccessResponse('Successfully unliking product');
  }

  public function rate(Request $request, $product) {
    if(!Setting::serviceIsActive('store')) {
      return $this->apiErrorResponse('This service has been deactivated');
    }

    $validator = Validator::make($request->all(), [
      'value' => 'required|numeric',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = $request->user();
    $product->linking($user);
    if($product->is_rated) {
      return $this->apiErrorResponse('You already rated this product');
    }
    $rates = $product->rates;
    $rates[$user->id] = $request->value;
    $product->rates = $rates;
    $product->unlinkingAndSave();
    return $this->apiSuccessResponse('Successfully rating product');
  }

}
