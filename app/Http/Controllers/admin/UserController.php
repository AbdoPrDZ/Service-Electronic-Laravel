<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\SocketBridge\SocketClient;
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
      'users' => $users,
    ]);
  }

  public function delete(Request $request, $id) {
    $user = User::find($id);
    if(is_null($user)) return $this->apiErrorResponse('Invlid user id');
    $user->delete();
    return $this->apiSuccessResponse('Successflully deleting user');
  }

  public function answoerIdentityVerify(Request $request, $userId) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|string',
      'refuse_description' => 'required|string',
    ]);

    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }

    $user = User::find($userId);
    if(!is_null($user)){
      $success = false;
      $message = '';
      if($request->status == 'accepeted') {
        $success = true;
        $message = 'Congratulations your identity verify eccepted';
        $user->identity_verifited_at = Carbon::now();
        $user->save();
      } else {
        $message = $request->refuse_description ?? 'Sorry your identity verify refused';
      }
      $client = new SocketClient($user->id);
      $response = $client->emit('identity-verify-ansowred', $success, $message);
      if(!$response['success']) {
        $notificatino = [
          'token' => $user->messaging_token,
          'notification' => [
            'title' => "Identity Verify Result",
            'body' => $message,
          ],
          'android' => [
            'notification' => [
              'priority' => "high",
              'icon' => 'stock_ticker_update',
              'sound' => "default",
              'color' => '#7e55c3',
              'imageUrl' => env('APP_URL').'/file/currency-12',
            ]
          ],
          'data' => [
            'topic' => 'identity-verify-Result',
            'success' => $success,
            'message' => $message,
          ],
        ];
        $res = $client->pushNotification($notificatino);
        Log::info('send transfer update notification', [$res]);
      }
    }
  }

}
