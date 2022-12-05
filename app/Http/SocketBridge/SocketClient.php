<?php

namespace App\Http\SocketBridge;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Error;

class SocketClient {

  public $clientId;

  /**
   * @var Client
   */
  public $guzzleClient;

  /**
   * @var \Illuminate\Database\Eloquent\Model
   */
  public $user;

  /**
   * @param String|int $clientId;
   */
  public function __construct($clientId, $driver = null) {
    $this->clientId = $clientId;
    $this->user = app($driver ?? config('socket_bridge.manager.client_driver'))::find($clientId);
    $this->guzzleClient = new Client([
      'headers' => [
        'Content-Type' => 'application/json; charset=UTF-8',
        'Accept' => 'application/json',
        'Connection-Key' => config('socket_bridge.connection_key')
      ]
    ]);
  }

  public function emit($routeName, $title, $message, ...$args) {
    try {
      return json_decode($this->guzzleClient->post(
        config('socket_bridge.bridge.protocol').'://'
        .config('socket_bridge.bridge.host').':'
        .config('socket_bridge.bridge.port')
        .config('socket_bridge.bridge.path')
        .str_replace([':clientId', ':routeName'], [$this->clientId, $routeName], config('socket_bridge.bridge.from_manager')),
        [
          'form_params' => [
            'title' => $title,
            'message' => $message,
            'args' => json_encode(...$args)
          ],
        ]
      )->getBody()->getContents());
    } catch (\Throwable $th) {
      Log::error('emit to client', ['throw'=> $th, 'clientId' => $this->clientId, 'routeName' => $routeName, 'args' => $args]);
      return ['success' => false, 'throw' => $th];
    }
  }

  public function pushNotification($notification) {
    try {
      return json_decode($this->guzzleClient->post(
        config('socket_bridge.bridge.protocol').'://'
        .config('socket_bridge.bridge.host').':'
        .config('socket_bridge.bridge.port')
        .config('socket_bridge.bridge.path')
        .str_replace([':clientId'], [$this->clientId], config('socket_bridge.bridge.push_notification')),
        ['form_params' => [json_encode($notification)]]
      )->getBody()->getContents());
    } catch (\Throwable $th) {
      Log::error('notify to client', ['throw'=> $th, 'clientId' => $this->clientId, 'notification' => $notification]);
      return ['success' => false, 'throw' => $th];
    }
  }

  public function emitOrNotify($eventName, $title, $message, $notification, ...$args) {
    $response = $this->emit($eventName, $title, $message, ...$args);
    Log::info('emit response', [$response]);
    if(!$response->success) {
      return $this->pushNotification($notification);
    }
    return $response;
  }

  public function emitAndNotify($eventName, $title, $message, $notification, ...$args) {
    $response = $this->emit($eventName, $title, $message, ...$args);
    Log::info('emit response', [$response]);
    $notifyResponse = $this->pushNotification($notification);
    Log::info('notify response', [$response]);
    return [
      'emit_response' => $response,
      'notify_response' => $notifyResponse,
    ];
  }

}
