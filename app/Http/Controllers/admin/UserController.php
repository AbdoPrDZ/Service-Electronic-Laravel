<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\SocketBridge\SocketClient;
use App\Models\Admin;
use App\Models\File;
use App\Models\Mail;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Storage;
use Validator;

class UserController extends Controller {

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

  static function news($admin_id) {
    return count(User::news($admin_id)) + count(Transfer::news($admin_id, 'recharge')) + count(Transfer::news($admin_id, 'withdraw'));
  }

  static function readNews($admin_id) {
    User::readNews($admin_id);
    Transfer::readNews($admin_id, 'recharge');
    Transfer::readNews($admin_id, 'withdraw');
  }

  public function delete(Request $request, User $user) {
    if (is_null($user->email_verified_at)) $user->delete();
    else $user->preDelete();
    return $this->apiSuccessResponse('Successflully deleting user');
  }

  public function changeIdentityStatus(Request $request, User $user) {
    $validator = Validator::make($request->all(), [
      'status' => 'required|in:verifited,refused',
      'answer_description' => 'string',
    ]);

    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors(), 'all' => $request->all()]);
    }

    if($user->identity_verifited_at != null) {
      return $this->apiErrorResponse('This User already answered');
    }
    $message = '';
    if($request->status == 'verifited') {
      $message = 'Congratulations your identity verify accepted';
      $user->identity_verifited_at = Carbon::now();
    } else {
      $message = $request->answer_description ?? 'Sorry your identity verify refused';
    }
    $user->identity_status = $request->status;
    $user->unreades = Admin::unreades($request->user()->id);
    $user->save();

    Notification::create([
      'name' => 'notifications',
      'title' => 'Identity Verify Result',
      'message' => $message,
      'from_id' => $request->user()->id,
      'from_model' => Admin::class,
      'to_id' => $user->id,
      'to_model' => User::class,
      'data' => [
        'event_name' => 'identity-status-change',
        'data' => json_encode([
          'status' => $request->status,
        ])
      ],
      'image_id' => 'logo',
      'type' => 'emitOrNotify',
    ]);
    Mail::create([
      'title' => 'Identity Verify Result',
      'template_id' => Setting::userIdentityConfirmEmailTemplateId(),
      'data' => [
        '<-answer->' => $request->status,
        '<-answer_description->' => $request->answer_description,
        '<-datetime->' => Carbon::now(),
      ],
      'targets' => [$request->user()->id],
      'unreades' => Admin::unreades(),
    ]);
    return $this->apiSuccessResponse('Successfully changing status');
  }

  public function getNextNotificationId() {
    $id = 0;
    $last = Notification::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  public function sendNotification(Request $request, $user) {
    $validator = Validator::make($request->all(), [
      'message' => 'required|string',
      'image' => 'file|mimes:jpg,png,jpeg',
    ]);

    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors()
      ]);
    }

    $notificationId = $this->getNextNotificationId();

    $values = [
      'id' => $notificationId,
      'name' => 'admin-message',
      'from_id' => $request->user()->id,
      'from_model' => User::class,
      'to_id' => $user->id,
      'to_model' => User::class,
      'title' => 'Admin message',
      'message' => $request->message,
      'image_id' => 'logo',
      'type' => 'emitAndNotify',
    ];

    if(!is_null($request->file('image'))) {
      if(!Storage::disk('api')->exists('users_data')) {
        Storage::disk('api')->makeDirectory('users_data');
      }
      $userFilesPath = "users_data/$user->id";
      if(!Storage::disk('api')->exists($userFilesPath)) {
        Storage::disk('api')->makeDirectory($userFilesPath);
      }
      $notificationFilesPath = "users_data/$user->id/notifications";
      if(!Storage::disk('api')->exists($notificationFilesPath)) {
        Storage::disk('api')->makeDirectory($notificationFilesPath);
      }
      $allpath = Storage::disk('api')->path("users_data/$user->id/notifications");
      $shortPath = "$userFilesPath/n-$notificationId.png";
      $request->file('image')->move($allpath, "n-$notificationId.png");
      File::create([
        'name' => "u-$user->id-n-$notificationId",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $values['data'] = [
        'attachment_image' => "u-$user->id-n-$notificationId",
      ];
    }

    Notification::create($values);
    return $this->apiSuccessResponse('Successfully sending message');
  }

  public function details(Request $request, User $user) {
    $user->linking();
    $items = Transfer::where('user_id', '=', $user->id)->get();
    $transfers = [];
    foreach ($items as $transfer) {
      $transfer->linking();
      $transfers[$transfer->id] = $transfer;
    }
    $products = $user->seller?->products();
    $sellerPurchases = $user->seller?->purchases();
    $purchases = $user->purchases();
    return view('admin.user-details', [
      'admin' => $request->user(),
      'user' => $user,
      'transfers' => $transfers,
      'products' => $products,
      'sellerPurchases' => $sellerPurchases,
      'purchases' => $purchases,
    ]);
  }

}
