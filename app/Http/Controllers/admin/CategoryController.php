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

    $categoryId = Category::getNextSequenceValue();

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
      'unreades' => Admin::unreades($request->user()->id),
    ]);
    return $this->apiSuccessResponse('Succesfully creating category');
  }

  public function edit(Request $request, Category $category) {
    $validator = Validator::make($request->all(), [
      'names' => 'string',
      'image' => 'file|mimes:jpg,png,jpeg',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

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
    $category->unreades = Admin::unreades($request->user()->id);
    $category->save();

    if($request->file('image')) {
      if(!Storage::disk('public')->exists("categories")) {
        Storage::disk('public')->makeDirectory("categories");
      }
      $request->file('image')->move(Storage::disk('public')->path("categories"), "$category->id.png");
    }

    return $this->apiSuccessResponse('Succesfully editing category');
  }

  public function delete(Request $request, $category) {
    if(is_null($category)) return $this->apiErrorResponse('Invlid category id');
    $category->preDelete();
    return $this->apiSuccessResponse('Successflully deleting category');
  }

}
