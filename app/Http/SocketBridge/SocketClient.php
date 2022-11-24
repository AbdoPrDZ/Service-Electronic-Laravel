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
  public function __construct($clientId) {
    $this->clientId = $clientId;
    $this->user = app(config('socket_bridge.manager.client_driver'))::find($clientId);
    $this->guzzleClient = new Client([
      'headers' => [
        'Content-Type' => 'application/json; charset=UTF-8',
        'Accept' => 'application/json',
        'Connection-Key' => config('socket_bridge.connection_key')
      ]
    ]);
  }

  public function emit($routeName, ...$args) {
    try {
      return $this->guzzleClient->post(
        config('socket_bridge.bridge.protocol').'://'
        .config('socket_bridge.bridge.host').':'
        .config('socket_bridge.bridge.port')
        .config('socket_bridge.bridge.path')
        .str_replace([':clientId', ':routeName'], [$this->clientId, $routeName], config('socket_bridge.bridge.from_manager')),
        [
          'body' => $args,
        ]
      )->getBody()->getContents();
    } catch (\Throwable $th) {
      Log::error('emit to client', ['throw'=> $th, 'clientId' => $this->clientId, 'routeName' => $routeName, 'args' => $args]);
      return ['success' => false, 'throw' => $th];
    }
  }

  public function pushNotification($notificatino) {
    try {
      return $this->guzzleClient->post(
        config('socket_bridge.bridge.protocol').'://'
        .config('socket_bridge.bridge.host').':'
        .config('socket_bridge.bridge.port')
        .config('socket_bridge.bridge.path')
        .str_replace([':clientId'], [$this->clientId], config('socket_bridge.bridge.push_notification')),
        ['form_params' => [json_encode($notificatino)]]
      )->getBody()->getContents();
    } catch (\Throwable $th) {
      Log::error('emit to client', ['throw'=> $th, 'clientId' => $this->clientId, 'notificatino' => $notificatino]);
      return ['success' => false, 'throw' => $th];
    }
  }


}
