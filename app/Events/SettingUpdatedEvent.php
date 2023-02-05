<?php

namespace App\Events;

use App\Http\SocketBridge\SocketClient;
use App\Models\Setting;
use App\Models\User;
use Cache;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingUpdatedEvent{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Setting $setting) {
    if(!Cache::store('file')->has('api/users-listens')) {
      Cache::store('file')->set('api/users-listens', []);
    }
    $ids = Cache::store('file')->get('api/users-listens');
    foreach ($ids as $id) {
      $user = User::find($id);
      if($user) {
        $user->linking();
        $client = new SocketClient($id, 'api', User::class);
        $client->emit('user-update', ['user' => $user]);
      }
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
