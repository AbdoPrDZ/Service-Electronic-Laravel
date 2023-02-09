<?php

namespace App\Events;

use App\Models\Exchange;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExchangeCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Exchange $exchange) {
    $exchange->linking();
    if($exchange->from_wallet) {
      $exchange->from_wallet->checking_withdraw_balance += $exchange->sended_balance;
      $exchange->from_wallet->balance -= $exchange->sended_balance;
      $exchange->from_wallet->unlinkingAndSave();
    }
    if($exchange->to_wallet) {
      $exchange->to_wallet->checking_recharge_balance += $exchange->received_balance;
      $exchange->to_wallet->unlinkingAndSave();
    }
    $exchange->linking();
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
