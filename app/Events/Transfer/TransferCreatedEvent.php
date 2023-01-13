<?php

namespace App\Events\Transfer;

use App\Models\Admin;
use App\Models\Notification;
use App\Models\Transfer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCreatedEvent {
  use Dispatchable, InteractsWithSockets, SerializesModels;


  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Transfer $transfer) {
    $transfer->linking();
    foreach ($transfer->unreades ?? [] as $admin_id) {
      $values = [
        'to_id' => $admin_id,
        'to_model' => Admin::class,
        'data' => [
          'transfer_id' => $transfer->id,
        ],
        'image_id' => 'logo',
        'type' => 'emit',
      ];
      if($transfer->for_what == 'transfer') {
        $values['name'] = 'new-transfer-created';
        $values['title'] = 'New Transfer Add from user (#' . $transfer->user->id . ')';
        $values['message'] = 'User (' .
          $transfer->user->id . ' - ' . $transfer->user->firstname . ' ' . $transfer->user->lastname .
          ') added new transfer with (send: ' .
          $transfer->received_balance .
          $transfer->received_currency->char .
          ', receive: ' .
          $transfer->sended_balance .
          $transfer->sended_currency->char .
          ')';
      } else if($transfer->for_what == 'withdraw') {
        $values['name'] = 'new-withdraw-created';
        $values['title'] = 'User (#' . $transfer->user->id . ') wants to withdraw his balance';
        $values['message'] = 'User (' .
          $transfer->user->id . ' - ' . $transfer->user->firstname . ' ' . $transfer->user->lastname .
          ') wants to withdraw his balance to (' .
          $transfer->received_balance .
          $transfer->received_currency->char .
          ') with balance ( ' .
          $transfer->sended_balance .
          $transfer->sended_currency->char .
          ')';
      } else if($transfer->for_what == 'recharge') {
        $values['name'] = 'new-recharge-created';
        $values['title'] = 'User (#' . $transfer->user->id . ') wants to recharge his account';
        $values['message'] = 'User (' .
          $transfer->user->id . ' - ' . $transfer->user->firstname . ' ' . $transfer->user->lastname .
          ') wants to recharge his account (balance: ' .
          $transfer->received_balance .
          $transfer->received_currency->char .
          ', sended ( ' .
          $transfer->sended_balance .
          $transfer->sended_currency->char .
          ')';
      }
      Notification::create($values);
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
