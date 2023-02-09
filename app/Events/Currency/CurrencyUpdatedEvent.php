<?php

namespace App\Events\Currency;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\Notification;
use App\Models\User;
use Cache;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CurrencyUpdatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Currency $currency) {
    $currency->linking();
    async(function () {
      if (!Cache::store('file')->has('api/users-listens')) {
        Cache::store('file')->set('api/users-listens', []);
      }
      $ids = Cache::store('file')->get('api/users-listens');
      foreach ($ids as $id) {
        User::find($id)?->emitUpdates();
      }
    })->start();
    foreach ($currency->unreades ?? [] as $admin_id) {
      Notification::create([
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'name' => 'currency-updated',
        'title' => 'A currency updated',
        'message' => 'Currency (' .
                      $currency->name . ' ' .
                      $currency->char . ')',
        'data' => [
          'currency_id' => $currency->id,
        ],
        'image_id' => 'transfers',
        'type' => 'emit',
      ]);
    }
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    return new PrivateChannel('channel-name');
  }
}
