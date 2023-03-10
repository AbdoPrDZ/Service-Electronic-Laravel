<?php

namespace App\Events\OfferRequest;

use App\Models\Admin;
use App\Models\Notification;
use App\Models\OfferRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferRequestCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(OfferRequest $offerRequest) {
    $offerRequest->linking();
    foreach ($offerRequest->unreades ?? [] as $admin_id) {
      Notification::create([
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'name' => 'new-offer-request-created',
        'title' => 'A new offer request created',
        'message' => 'Offer request created (title: ' .
                    $offerRequest->offer->title['en'] . ', Offer: ' .
                    $offerRequest->offer->sub_offers[$offerRequest->sub_offer]['title_en'] . ', Price: ' .
                    $offerRequest->total_price . ' DZD)',
        'data' => [
          'offer_id' => $offerRequest->id,
        ],
        'image_id' => 'offers',
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
