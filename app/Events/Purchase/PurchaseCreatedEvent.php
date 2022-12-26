<?php

namespace App\Events\Purchase;

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

class PurchaseCreatedEvent
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Purchase $purchase) {
    $purchase->linking();
    // $admins = Admin::all();
    // foreach ($admins as $admin) {
    //   $admin_id = $admin->id;
    foreach ($purchase->unreades as $admin_id) {
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
          'product_id' => $purchase->product->id,
        ],
        'image_id' => 'currency-4',
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
