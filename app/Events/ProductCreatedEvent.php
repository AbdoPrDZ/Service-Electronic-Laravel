<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Notification;
use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Product $product) {
    $product->linking();
    foreach ($product->unreades ?? [] as $admin_id) {
      Notification::create([
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'name' => 'new-product-created',
        'title' => 'A new product created',
        'message' => 'product created (Name: ' .
                      $product->name . ', Price: ' .
                      $product->price . ' DZD, Seller: ' .
                      $product->seller->user->fullname . ')',
        'data' => [
          'product_id' => $product->id,
        ],
        'image_id' => 'store',
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
