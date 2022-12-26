<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  public function all(Request $request) {
    $notifications = Notification::allUnreaded($request->user()->id);
    $fNotifications = [];
    foreach ($notifications as $notification) {
      $notification->linking();
      $fNotifications[$notification->id] = $notification;
    }
    return $fNotifications;
  }

  public function markAsRead(Request $request, $id)  {
    $notification = Notification::find($id);
    if($notification) {
      $notification->is_readed = true;
      $notification->save();
      return $this->apiSuccessResponse('Successfully marking as readed');
    }
    return $this->apiErrorResponse('Invalid notfication id');
  }

}
