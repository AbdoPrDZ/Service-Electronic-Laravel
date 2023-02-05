<?php

namespace App\Events\Seller;

use App\Http\SocketBridge\SocketClient;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Seller;
use Cache;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SellerCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Seller $seller) {
    $seller->linking();
    if(!Cache::store('file')->has('api/users-listens')) {
      Cache::store('file')->set('api/users-listens', []);
    }
    $ids = Cache::store('file')->get('api/users-listens');
    if (!in_array($seller->user->id, $ids)) return;
    $seller->user->linking();
    $client = new SocketClient($seller->user->id, 'api', User::class);
    $client->emit('user-update', ['user' => $seller->user]);

    foreach ($seller->unreades ?? [] as $admin_id) {
      Notification::create([
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'name' => 'new-seller-created',
        'title' => 'A new seller created',
        'message' => 'Seller created (Name: ' .
                      $seller->store_name . ', Address: ' .
                      $seller->store_address . ', User: ' .
                      $seller->user->fullname . ')',
        'data' => [
          'seller_id' => $seller->id,
        ],
        'image_id' => $seller->store_image_id,
        'type' => 'emit',
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
