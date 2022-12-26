<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Seller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\File;
use Validator;

class ProductController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:sanctum');
  }

  public function getNextId() {
    $id = 0;
    $last = Product::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  public function all(Request $request) {
    $data = Product::all();
    $items = [];
    foreach ($data as $item) {
      $item->linking($request->user());
      $items[$item->id] = $item;
    }
    return $this->apiSuccessResponse('Successfully getting products', [
      'currencies' => $items,
    ]);
  }

  public function find($id) {
    $product = Product::find($id);
    if(!is_null($product)) {
      return $product;
    } else {
      return $this->apiErrorResponse('Invalid id',[], 404);
    }
  }

  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'description' => 'required|string',
      'price' => 'required|regex:/^[0-9]+(\.[0-9]?[0-9]?[0-9]?)?$/',
      'count' => 'required|integer',
      'category_id' => 'required|integer',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = $request->user();

    $seller = Seller::where('user_id', '=', $user->id)->first();
    if(is_null($seller)) {
      return $this->apiErrorResponse('You are not seller');
    }
    if($user->identity_verifited_at == null) {
      return $this->apiErrorResponse('You identity not verifited');
    }
    if(is_null(Category::where('id', '=', $request->category_id)->first())) {
      return $this->apiErrorResponse('Invalid category');
    }

    $commission = Setting::find('commission')?->value[0];

    $productId = $this->getNextId();
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
    foreach ($request->files as $file) {
      $file->move(Storage::disk('api')->path($userProductPath), "i-$i");
      $name = "u-" . $user->id . "-p-$productId-i-$i";
      File::create([
        'name' => $name,
        'disk' => 'api',
        'type' => 'image',
        'path' => "$userProductPath/i-$i",
      ]);
      $values['images_ids'][] = $name;
      $i++;
    }
    $values['images_ids'] = $values['images_ids'];

    Product::create($values);

    return response()->json([
      'success' => true,
      'message' => 'Successfully creating product',
    ]);

  }

  public function edit(Request $request, $id) {
    $validator = Validator::make($request->all(), [
      'name' => 'string',
      'description' => 'string',
      'price' => 'regex:/^[0-9]+(\.[0-9]?[0-9]?[0-9]?)?$/',
      'pricing_type' => 'string',
      'category_id' => 'integer',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'messages' => $validator->errors(),
      ]);
    }

    $seller = Seller::where('user_id', '=', $request->user()->id)->first();
    if(is_null($seller)) {
      return $this->apiErrorResponse('You are not seller');
    }
    if($request->user()->identity_verifited_at == null) {
      return $this->apiErrorResponse('You identity not verifited');
    }

    $product = Product::find($id);
    if($product->seller_id != $seller->id) {
      return $this->apiErrorResponse('Bad request.', [], 400);
    }

    if(!is_null($product)) {
      // $product->update($request->all());
      $product->name = $request->name ?? $product->name;
      $product->description = $request->description ?? $product->description;
      $product->price = $request->price ?? $product->price;
      $product->pricing_type = $request->pricing_type ?? $product->pricing_type;
      $product->category_id = $request->category_id ?? $product->category_id;
      $product->save();
      return response()->json([
        'success' => true,
        'message' => 'Successfully editing product'
      ]);
    } else {
      return response()->json([
        'success' => false,
        'message' => 'Invalid product id'
      ]);
    }
  }

  public function like(Request $request, $id) {
    $user = $request->user();
    $product = Product::find($id);
    if($product) {
      $product->linking($user);
      if($product->is_liked) {
        return $this->apiErrorResponse('You already liked this product');
      }
      $product->likes = [...$product->likes, $user->id];
      $product->unlinkingAndSave();
      return $this->apiSuccessResponse('Successfully liking product');
    } else {
      return $this->apiErrorResponse('Invalid product id');
    }
  }

  public function unLike(Request $request, $id) {
    $user = $request->user();
    $product = Product::find($id);
    if($product) {
      $product->linking($user);
      if(!$product->is_liked) {
        return $this->apiErrorResponse('You are not liked this product');
      }
      $product->likes = array_diff($product->likes, [$user->id]);
      $product->unlinkingAndSave();
      return $this->apiSuccessResponse('Successfully unliking product');
    } else {
      return $this->apiErrorResponse('Invalid product id');
    }
  }

  public function rate(Request $request, $id) {
    $validator = Validator::make($request->all(), [
      'value' => 'required|regex:/^[0-9]+(\.[0-9]?[0-9]?[0-9]?)?$/',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $user = $request->user();
    $product = Product::find($id);
    if($product) {
      $product->linking($user);
      if($product->is_rated) {
        return $this->apiErrorResponse('You already rated this product');
      }
      $rates = $product->rates;
      $rates[$user->id] = $request->value;
      $product->rates = $rates;
      $product->unlinkingAndSave();
      return $this->apiSuccessResponse('Successfully rating product');
    } else {
      return $this->apiErrorResponse('Invalid product id');
    }
  }

}
