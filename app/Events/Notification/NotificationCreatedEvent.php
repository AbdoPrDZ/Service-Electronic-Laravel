<?php

namespace App\Events\Notification;

use App\Http\SocketBridge\SocketClient;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;

class NotificationCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Notification $notification) {
    $notification->linking();
    $room = [User::class => 'api', Admin::class => 'admin'][$notification->to_model];
    $client = new SocketClient($notification->to_id, $room);
    try {
      if($notification->type == 'emit') {
        $client->emitNotification($notification);
      } else {
        if($notification->type == 'notify') $client->pushNotification($notification);
        else if($notification->type == 'emitOrNotify') $client->emitOrPushNotification($notification);
        else if($notification->type == 'emitAndNotify') $client->emitAndPushNotification($notification);
      }
    } catch (\Throwable $th) {
      Log::error("notifcation event", [$th]);
    }
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn() {
    return new PrivateChannel('channel-name');
  }
}
