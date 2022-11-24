<?php

namespace App\Events;

use App\Http\SocketBridge\SocketClient;
use App\Http\SocketBridge\SocketManager;
use App\Models\Transfer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TransferEvent
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Transfer $transfer) {
    $transfer->linking();
    if($transfer->ansowerd_at == null && $transfer->status != 'checking') {
      $client = new SocketClient($transfer->user_id);
      $response = json_decode($client->emit('transfer-ansower', $transfer->id, $transfer->status));
      Log::info('emit response', [$response]);
      if(!$response->success) {
        $ansowers = [
          'eccepted' => 'Congratulations, your transfer request has been accepted',
          'refused' => 'Unfortunately your transfer request has been refused, for more information please contact support.'
        ];
        $notificatino = [
          'token' => $transfer->user->messaging_token,
          'notification' => [
            'title' => "Transfer (#$transfer->id) Status Changed",
            'body' => $ansowers[$transfer->status],
          ],
          'android' => [
            'notification' => [
              'priority' => "high",
              'icon' => 'stock_ticker_update',
              'sound' => "default",
              'color' => '#7e55c3',
              'imageUrl' => 'http://abdopr.ddns.net/file/currency-12',
            ]
          ],
          'data' => [
            'id' => "$transfer->id",
            'status' => $transfer->status,
          ],
        ];
        $res = $client->pushNotification($notificatino);
        Log::info('send transfer update notification', [$res]);
      }
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
