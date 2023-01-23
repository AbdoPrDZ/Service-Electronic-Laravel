<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Notification;
use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Offer $offer) {
    foreach ($offer->unreades ?? [] as $admin_id) {
      Notification::create([
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'name' => 'new-offer-created',
        'title' => 'A new offer created',
        'message' => 'Offer created (' . $offer->title['en'] . ', ' . $offer->description['en'] . ')',
        'data' => [
          'offer_id' => $offer->id,
        ],
        'image_id' => $offer->image_id,
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
