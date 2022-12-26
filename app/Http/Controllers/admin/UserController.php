<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\SocketBridge\SocketClient;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Validator;

class UserController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  static function all(Request $request) {
    $items = User::all();
    $users = [];
    foreach ($items as $value) {
      $value->linking();
      $users[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => [
        'users' => $users,
        'recharges' => Transfer::recharges(),
        'withdrawes' => Transfer::withdrawes(),
      ]
    ]);
  }

  static function news(Request $request) {
    $admin_id = $request->user()->id;
    return [
      'count' => count(User::news($admin_id)) + count(Transfer::news($admin_id, 'recharge')) + count(Transfer::news($admin_id, 'withdraw')),
    ];
  }

  static function readNews(Request $request) {
    User::readNews($request->user()->id);
    Transfer::readNews($request->user()->id, 'recharge');
    Transfer::readNews($request->user()->id, 'withdraw');
    return Controller::apiSuccessResponse('successfully reading news');
  }

  public function delete(Request $request, $id) {
    $user = User::find($id);
    if(is_null($user)) return $this->apiErrorResponse('Invlid user id');
    $user->delete();
    return $this->apiSuccessResponse('Successflully deleting user');
  }

  public function changeIdentityStatus(Request $request, $userId) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|in:verifited,refused',
      'answer_description' => 'string',
    ]);

    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }

    $user = User::find($userId);
    if(!is_null($user)){
      if($user->identity_verifited_at != null) {
        return $this->apiErrorResponse('This User already ansowered');
      }
      $message = '';
      if($request->status == 'verifited') {
        $message = 'Congratulations your identity verify accepted';
        $user->identity_verifited_at = Carbon::now();
      } else {
        $message = $request->answer_description ?? 'Sorry your identity verify refused';
      }
      $user->identity_status = $request->status;
      $user->save();

      Notification::create([
        'name' => 'notifications',
        'title' => 'Identity Verify Result',
        'message' => $message,
        'from_id' => $user->id,
        'from_model' => Admin::class,
        'to_id' => $userId,
        'to_model' => User::class,
        'data' => [
          'event_name' => 'identity-status-change',
          'data' => json_encode([
            'status' => $request->status,
          ])
        ],
        'image_id' => 'currency-4',
        'type' => 'emitOrNotify',
      ]);
      return $this->apiSuccessResponse('Successfully changing status');
    }
  }

}
