<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller {

  public function all(Request $request) {
    $notifications = Notification::where([
      ['name', '=', 'admin-message'],
      ['to_id', '=', $request->user()->id],
      ['to_model', '=', User::class],
      ['is_readed', '=', 0]
    ])->get();
    $fNotifications = [];
    foreach ($notifications as $notification) {
      $notification->linking();
      $fNotifications[$notification->id] = $notification;
    }
    return $this->apiSuccessResponse('Successfully getting notifications', [
      'notifications' => $fNotifications
    ]);
  }

  public function markAsRead(Request $request, Notification $notification)  {
    if($notification) {
      $notification->is_readed = true;
      $notification->save();
      return $this->apiSuccessResponse('Successfully marking as readed');
    }
    return $this->apiErrorResponse('Invalid notfication id');
  }

}
