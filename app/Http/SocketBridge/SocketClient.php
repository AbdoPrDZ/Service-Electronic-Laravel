<?php

namespace App\Http\SocketBridge;

use App\Models\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Error;

class SocketClient {

  public $room;

  /**
   * @var string|int $clientId;
   */
  public $clientId;

  /**
   * @var Client
   */
  public $guzzleClient;

  /**
   * @var \Illuminate\Database\Eloquent\Model
   */
  public $user;

  public function __construct($clientId, $room, $driver = null) {
    $this->clientId = $clientId;
    $this->room = $room;
    $this->user = app($driver ?? config("socket_bridge.manager.rooms.$this->room.client_driver"))::find($clientId);
    $this->guzzleClient = new Client([
      'headers' => [
        'Content-Type' => 'application/json; charset=UTF-8',
        'Accept' => 'application/json',
        'Connection-Key' => config('socket_bridge.connection_key')
      ]
    ]);
  }

  public function emit(string $routeName, array $args) {
    // try {
      return json_decode($this->guzzleClient->post(
        config('socket_bridge.bridge.protocol').'://'
        .config('socket_bridge.bridge.host').':'
        .config('socket_bridge.bridge.port')
        .config('socket_bridge.bridge.path')
        .str_replace(
          [':room', ':clientId', ':routeName'],
          [$this->room, $this->clientId, $routeName],
          config('socket_bridge.bridge.from_manager')
        ),
        [
          'form_params' => $args,
        ]
      )->getBody()
       ->getContents());
    // } catch (\Throwable $th) {
    //   Log::error('emit to client', ['throw' => $th, 'room' => $this->room, 'clientId' => $this->clientId, 'routeName' => $routeName, 'args' => $args]);
    //   return ['success' => false, 'throw' => $th];
    // }
  }

  public function emitNotification(Notification $notification) {
    try {
      return json_decode($this->guzzleClient->post(
        config('socket_bridge.bridge.protocol').'://'
        .config('socket_bridge.bridge.host').':'
        .config('socket_bridge.bridge.port')
        .config('socket_bridge.bridge.path')
        .str_replace([':room', ':clientId'], [$this->room, $this->clientId], config('socket_bridge.bridge.emit_notification')),
        ['form_params' => [json_encode($notification)]]
      )->getBody()->getContents());
    } catch (\Throwable $th) {
      Log::error('notify to client', ['throw' => $th, 'room' => $this->room, 'clientId' => $this->clientId, 'notification' => $notification]);
      return ['success' => false, 'throw' => $th];
    }
  }

  public function pushNotification(Notification $notification) {
    try {
      return json_decode($this->guzzleClient->post(
        config('socket_bridge.bridge.protocol').'://'
        .config('socket_bridge.bridge.host').':'
        .config('socket_bridge.bridge.port')
        .config('socket_bridge.bridge.path')
        .str_replace([':room', ':clientId'], [$this->room, $this->clientId], config('socket_bridge.bridge.push_notification')),
        ['form_params' => [json_encode($notification)]]
      )->getBody()->getContents());
    } catch (\Throwable $th) {
      Log::error('notify to client', ['throw' => $th, 'room' => $this->room, 'clientId' => $this->clientId, 'notification' => $notification]);
      return ['success' => false, 'throw' => $th];
    }
  }

  public function emitOrPushNotification(Notification $notification) {
    $response = $this->emitNotification($notification);
    Log::info('emit response', [$response]);
    if(!$response->success) {
      return $this->pushNotification($notification);
    }
    return $response;
  }

  public function emitAndPushNotification(Notification $notification) {
    $response = $this->emitNotification($notification);
    Log::info('emit response', [$response]);
    $notifyResponse = $this->pushNotification($notification);
    Log::info('notify response', [$response]);
    return [
      'emit_response' => $response,
      'notify_response' => $notifyResponse,
    ];
  }

}
