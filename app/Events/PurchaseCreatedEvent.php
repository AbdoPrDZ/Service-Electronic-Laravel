<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Notification;
use App\Models\Purchase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Purchase $purchase) {
    $purchase->linking();
    Notification::create([
      'to_id' => $purchase->product->seller->user_id,
      'to_model' => User::class,
      'name' => 'new-product-solded',
      'title' => 'A new product has been sold',
      'message' => 'New Product solded by User(' . $purchase->user->fullname . ')',
      'data' => [
        'event_name' => 'new-product-solded',
        'data' => json_encode([
          'product_id' => $purchase->product->id,
          'count' => $purchase->request->count,
          'purchase_id' => $purchase->id,
        ]),
      ],
      'image_id' => $purchase->product->images_ids[0],
      'type' => 'emitAndNotify',
    ]);
    foreach ($purchase->unreades ?? [] as $admin_id) {
      Notification::create([
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'name' => 'new-product-solded',
        'title' => 'A new product has been solded',
        'message' => 'Product (' .
                      $purchase->product->name .
                      ') solded by User (' .
                      $purchase->fullname .
                      ')',
        'data' => [
          'purchase_id' => $purchase->id,
        ],
        'image_id' => $purchase->product->images_ids[0],
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
