<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use Validator;

class FileController extends Controller {

  // public function __construct() {
  //   $this->middleware('auth:sanctum');
  // }

  public function find(Request $request, $filename) {
  $file = File::where('name', '=', $filename)->first();
  if(!is_null($file)) {
    Storage::disk($file->disk)->download($file->path);
    return response()->download(
    Storage::disk($file->disk)->path($file->path),
    $file->name,
    []
    );
  }
  }

  public function finds($client, ...$args) {
  print_r($client);
  print_r($args);
  }

}
