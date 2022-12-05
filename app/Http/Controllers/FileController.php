<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;

class FileController extends Controller {

  public function find(Request $request, $appId, $filename) {
    $file = File::where('name', '=', $filename)->first();
    if(!is_null($file)) {
      Storage::disk($file->disk)->download($file->path);
      return response()->download(
        Storage::disk($file->disk)->path($file->path),
        $file->name,
        []
      );
    } else {
      $this->throwResponse();
    }
  }

}
