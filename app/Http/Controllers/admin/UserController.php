<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\File;
use App\Models\Mail;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Storage;
use Validator;

class UserController extends Controller {

  static function all(Request $request) {
    $items = User::where('is_deleted', '=', 0)->get();
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
      'description' => $request->status == 'refused' ? 'required|string' : '',
    ]);

    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
        'all' => $request->all(),
      ]);
    }

    if($user->identity_verifited_at != null) {
      return $this->apiErrorResponse('This User already answered');
    }
    $message = '';
    if($request->status == 'verifited') {
      $message = 'Congratulations your identity verify accepted';
      $user->identity_verifited_at = now();
    } else {
      $message = $request->description ?? 'Sorry your identity verify refused';
    }
    $user->identity_status = $request->status;
    $user->identity_answer_description = $request->description;
    $user->unreades = Admin::unreades($request->user()->id);
    $user->save();

    Notification::create([
      'name' => 'identity-status-change',
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
        '<-answer_description->' => $request->description,
        '<-datetime->' => date_format(now(),"Y/m/d H:i:s"),
      ],
      'targets' => [$request->user()->id],
      'unreades' => Admin::unreades(),
    ]);
    return $this->apiSuccessResponse('Successfully changing status');
  }

  public function sendNotification(Request $request) {
    $request->merge(['targets' => $this->tryDecodeArray($request->targets)]);
    $validator = Validator::make($request->all(), [
      'message' => 'required|string',
      'image' => !is_null($request->file('image')) ? 'file|mimes:jpg,png,jpeg' : '',
      'targets' => 'required|array',
    ]);

    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors()
      ]);
    }

    if(count($request->targets) == 0) return $this->apiErrorResponse('targets must be not empty');

    $values = [
      'name' => 'admin-message',
      'from_id' => $request->user()->id,
      'from_model' => Admin::class,
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
      $id = $request->targets[0];
      $notificationId = Notification::getNextSequenceValue();
      $userFilesPath = "users_data/$id";
      if(!Storage::disk('api')->exists($userFilesPath)) {
        Storage::disk('api')->makeDirectory($userFilesPath);
      }
      $notificationFilesPath = "users_data/$id/notifications";
      if(!Storage::disk('api')->exists($notificationFilesPath)) {
        Storage::disk('api')->makeDirectory($notificationFilesPath);
      }
      $allpath = Storage::disk('api')->path("users_data/$id/notifications");
      $shortPath = "$userFilesPath/notifications/n-$notificationId";
      $request->file('image')->move($allpath, "n-$notificationId");
      File::create([
        'name' => "u-$id-n-$notificationId",
        'disk' => 'api',
        'type' => 'image',
        'path' => $shortPath,
      ]);
      $values['data'] = [
        'attachment_image' => "u-$id-n-$notificationId",
      ];
    }
    async(function () use ($request, $values) {
      foreach ($request->targets as $id) {
        if (User::find($id)) {
          $values['id'] = Notification::getNextSequenceValue();
          $values['to_id'] = $id;
          Notification::create($values);
        }
      }
    })->start();
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
