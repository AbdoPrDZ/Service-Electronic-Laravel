<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class CategoryController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  public function getNextId() {
    $id = 0;
    $last = Category::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  static function all(Request $request) {
    $items = Category::all();
    $categories = [];
    foreach ($items as $value) {
      $categories[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => $categories,
    ]);
  }

  static function news(Request $request) {
    $admin_id = $request->user()->id;
    return [
      'count' => count(Category::news($admin_id)),
    ];
  }

  static function readNews(Request $request) {
    Category::readNews($request->user()->id);
    return Controller::apiSuccessResponse('successfully reading news');
  }

  public function create(Request $request) {
    $validator = Validator::make($request->all(), [
      'names' => 'required|string',
      'image' => 'required|file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    try {
      $rNames = json_decode($request->names);
      $names = [];
      foreach ($rNames as $name) {
        $names[$name->lang_code] = $name->text;
      }
    } catch (\Throwable $th) {
      return $this->apiErrorResponse('Invalid Names');
    }

    $categoryId = $this->getNextId();

    if(!Storage::disk('public')->exists("categories")) {
      Storage::disk('public')->makeDirectory("categories");
    }
    $request->file('image')->move(Storage::disk('public')->path("categories"), "$categoryId.png");
    $imageFile = File::create([
      'name' => "category-$categoryId",
      'disk' => 'public',
      'type' => 'image',
      'path' => "categories/$categoryId.png",
    ]);

    Category::create([
      'id' => $categoryId,
      'name' => $names,
      'image_id' => $imageFile->name,
    ]);
    return $this->apiSuccessResponse('Succesfully creating category');
  }

  public function edit(Request $request, $id) {
    $validator = Validator::make($request->all(), [
      'names' => 'string',
      'image' => 'file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $category = Category::find($id);
    $names = [];
    if(!is_null($request->names)) {
      try {
        $rNames = json_decode($request->names);
        $names = [];
        foreach ($rNames as $name) {
          $names[$name->lang_code] = $name->text;
        }
        $category->name = $names;
      } catch (\Throwable $th) {
        return $this->apiErrorResponse('Invalid Names');
      }
    }
    $category->save();

    if($request->file('image')) {
      if(!Storage::disk('public')->exists("categories")) {
        Storage::disk('public')->makeDirectory("categories");
      }
      $request->file('image')->move(Storage::disk('public')->path("categories"), "$id.png");
    }

    return $this->apiSuccessResponse('Succesfully editing category');
  }

  public function delete(Request $request, $id) {
    $category = Category::find($id);
    if(is_null($category)) return $this->apiErrorResponse('Invlid category id');
    $category->delete();
    return $this->apiSuccessResponse('Successflully deleting category');
  }

}
