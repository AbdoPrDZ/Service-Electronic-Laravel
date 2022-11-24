<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\File;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class TransferController extends Controller {

  public function __construct() {
    $this->middleware('auth:sanctum');
  }

  public function all(Request $request) {
    $data = Transfer::where('user_id', '=', $request->user()->id)->get();
    $items = [];
    foreach ($data as $item) {
      $item->linking();
      $items[$item->id] = $item;
    }
    return [
      'success' => true,
      'message' => 'Successfully getting transfers',
      'currencies' => $items,
    ];
  }

  public function getNextId() {
  $id = 0;
  $last = Transfer::orderBy('id','desc')->first();
  if(!is_null($last)) {
    $id = $last->id;
  }
  return $id + 1;
  }

  public function create(Request $request) {
  $validator = Validator::make($request->all(), [
  'sended_balance' => 'required|string',
  'received_balance' => 'required|string',
  'sended_currency_id' => 'required|string',
  'received_currency_id' => 'required|string',
  'wallet' => 'required|string',
  'proof' => 'file|mimes:jpg,png,jpeg'
  ]);
  if ($validator->fails()) {
    return response()->json([
    'success' => false,
    'messages' => $validator->errors(),
    ], 422);
  }
  $transferId = $this->getNextId();
  $values = [
    'id' => $transferId,
    'user_id' => $request->user()->id,
    'sended_balance' => $request->sended_balance,
    'received_balance' => $request->received_balance,
    'sended_currency_id' => $request->sended_currency_id,
    'received_currency_id' => $request->received_currency_id,
    'wallet' => $request->wallet,
  ];
  if(!is_null($request->file('proof'))) {
    if(!Storage::disk('api')->exists('users_data')) {
    Storage::disk('api')->makeDirectory('users_data');
    }
    $userFilesPath = 'users_data/' . $request->user()->id;
    if(!Storage::disk('api')->exists($userFilesPath)) {
    Storage::disk('api')->makeDirectory($userFilesPath);
    Storage::disk('api')->makeDirectory($userFilesPath . '/transfers');
    }
    $allpath = Storage::disk('api')->path($userFilesPath . '/transfers');
    $shortPath = $userFilesPath . '/transfers/t-' . $transferId;
    $request->file('proof')->move($allpath, 't-' . $transferId);
    File::create([
      'name' => 'u-' . $request->user()->id . '-t-' . $transferId,
      'disk' => 'api',
      'type' => 'image',
      'path' => $shortPath,
    ]);
    $values['proof_id'] = 'u-' . $request->user()->id . '-t-' . $transferId;
  }

  Transfer::create($values);

  return response()->json([
    'success' => true,
    'message' => 'Successfully add transfer',
    ], 200);
  }

}
