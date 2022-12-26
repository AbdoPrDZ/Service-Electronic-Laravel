<?php

namespace App\Events;

use App\Http\SocketBridgeNew\SocketClientNew;
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
use Illuminate\Support\Facades\Log;

class NotificationCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Notification $notification) {
    $notification->linking();
    Log::info('send notification', [$notification]);
    $rooms = [
      User::class => 'api',
      Admin::class => 'admin',
    ];
    $client = new SocketClientNew($notification->to_id, $rooms[$notification->to_model]);
    if($notification->type == 'emit') {
      $client->emit($notification->name, $notification->title, $notification->message, $notification);
    } else {
      $data = [
        'token' => $notification->client->messaging_token,
        'notification' => [
          'title' => $notification->title,
          'body' => $notification->message,
        ],
        'android' => [
          'notification' => [
            'priority' => "high",
            'icon' => 'stock_ticker_update',
            'sound' => "default",
            'color' => '#7e55c3',
            'imageUrl' => env('APP_URL') . '/file/public/currency-4',
          ]
        ],
        'data' => [
          'notification_id' => "$notification->id",
          ...$notification->data,
        ],
      ];
      if($notification->type == 'notify') $client->pushNotification($data);
      if($notification->type == 'emitOrNotify') $client->emitOrNotify(
        $notification->name, $notification->title,
        $notification->message, $data, [
          'notification_id' => "$notification->id",
          ...$notification->data,
        ]);
      if($notification->type == 'emitAndNotify') $client->emitAndNotify(
        $notification->name, $notification->title,
        $notification->message, $data, [
          'notification_id' => "$notification->id",
          ...$notification->data,
        ]);
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
