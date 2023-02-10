<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class CategoryController extends Controller {

  static function news(Request $request) {
    $admin_id = $request->user()->id;
    return count(Category::news($admin_id));
  }

  static function readNews(Request $request) {
    Category::readNews($request->user()->id);
  }

  public function create(Request $request) {
    $request->merge(['names' => $this->tryDecodeArray($request->names)]);
    $validator = Validator::make($request->all(), [
      'names' => 'required|array',
      'image' => 'required|file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $categoryId = Category::getNextSequenceValue();

    if(!Storage::disk('public')->exists("categories")) {
      Storage::disk('public')->makeDirectory("categories");
    }
    $time = now()->timestamp;
    $request->file('image')->move(Storage::disk('public')->path("categories"), "$categoryId-$time");
    $imageFile = File::create([
      'name' => "category-$categoryId-$time",
      'disk' => 'public',
      'type' => 'image',
      'path' => "categories/$categoryId-$time",
    ]);

    Category::create([
      'id' => $categoryId,
      'name' => $request->names,
      'image_id' => $imageFile->name,
      'unreades' => Admin::unreades($request->user()->id),
    ]);
    return $this->apiSuccessResponse('Succesfully creating category');
  }

  public function edit(Request $request, Category $category) {
    $request->merge(['names' => $this->tryDecodeArray($request->names)]);
    $validator = Validator::make($request->all(), [
      'names' => 'array',
      'image' => 'file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    if(!is_null($request->names)) {
        $category->name = $request->names;
    }
    $category->unreades = Admin::unreades($request->user()->id);

    if($request->file('image')) {
      File::find($category->image_id)?->delete();
      if(!Storage::disk('public')->exists("categories")) {
        Storage::disk('public')->makeDirectory("categories");
      }
      $time = now()->timestamp;
      $request->file('image')->move(Storage::disk('public')->path("categories"), "$category->id-$time");
      $imageFile = File::create([
        'name' => "category-$category->id-$time",
        'disk' => 'public',
        'type' => 'image',
        'path' => "categories/$category->id-$time",
      ]);
      $category->image_id = $imageFile->name;
    }
    $category->save();

    return $this->apiSuccessResponse('Succesfully editing category');
  }

  public function delete(Request $request, $category) {
    if(is_null($category)) return $this->apiErrorResponse('Invlid category id');
    $category->preDelete();
    return $this->apiSuccessResponse('Successflully deleting category');
  }

}
