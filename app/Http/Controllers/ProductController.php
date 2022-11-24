<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\File;
use Validator;

class ProductController extends Controller {

  public function __construct() {
    $this->middleware('auth:sanctum');
  }

  public function getNextId() {
    $id = 0;
    $last = Product::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  public function all() {
    $data = Product::all();
    $items = [];
    foreach ($data as $item) {
      $item->linking();
      $items[$item->id] = $item;
    }
    return [
      'success' => true,
      'message' => 'Successfully getting products',
      'currencies' => $items,
    ];
  }

  public function find($id) {
    $product = Product::find($id);
    if(!is_null($product)) {
      return $product;
    } else {
      return response()->json([
        'success' => false,
        'message' => 'Invalid id'
      ], 404);
    }
  }

  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'description' => 'required|string',
      'price' => 'required',
      'category_id' => 'required|string',
      'tags' => 'required|string',
      // 'images' => 'required|files|mimes:jpg,png,jpeg',
      // 'proof' => 'required|file|mimes:jpg,png,jpeg'
    ]);
    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'errors' => $validator->errors(),
      ]);
    }

    if(is_null(Category::where('id', '=', $request->category_id)->first())) {
      return $this->apiErrorResponse('Invalid category');
    }

    $productId = $this->getNextId();
    $values = [
      'id' => $productId,
      'user_id' => $request->user()->id,
      'name' => $request->name,
      'description' => $request->description,
      'price' => $request->price,
      'category_id' => $request->category_id,
      'tags' => $request->tags,
      'images_ids' => [],
    ];

    if(!Storage::disk('api')->exists('users_data')) {
      Storage::disk('api')->makeDirectory('users_data');
    }
    $userFilesPath = 'users_data/' . $request->user()->id;
    if(!Storage::disk('api')->exists($userFilesPath)) {
      Storage::disk('api')->makeDirectory($userFilesPath);
      Storage::disk('api')->makeDirectory($userFilesPath . '/products');
    }
    $userProductPath = $userFilesPath . '/products/' . $productId;
    if(!Storage::disk('api')->exists($userProductPath)) {
      Storage::disk('api')->makeDirectory($userProductPath);
    }

    $i = 1;
    foreach ($request->files as $file) {
      $file->move(Storage::disk('api')->path($userProductPath), 'i-' . $i);
      $name = 'u-' . $request->user()->id . '-p-' . $productId . '-i-' . $i;
      File::create([
        'name' => $name,
        'disk' => 'api',
        'type' => 'image',
        'path' => $userProductPath . '/i-' . $i,
      ]);
      $values['images_ids'][] = $name;
      $i++;
    }
    $values['images_ids'] = json_encode($values['images_ids']);

    Product::create($values);

    return response()->json([
      'success' => true,
      'message' => 'Successfully creating product',
    ]);

  }

  public function edit(Request $request, $id) {
    $validator = Validator::make($request->all(), [
      'name' => 'string',
      'description' => 'string|min:15',
      'price' => 'integer',
      'category' => 'integer',
      'tags' => 'string',
      // 'images' => 'files|mimes:jpg,png,jpeg',
      // 'proof' => 'file|mimes:jpg,png,jpeg'
    ]);
    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'messages' => $validator->errors(),
      ]);
    }

    $product = Product::find($id);

    if(!is_null($product)) {
      $product->update($request->all());
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
}
