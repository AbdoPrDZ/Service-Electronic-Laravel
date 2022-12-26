<?php

namespace App\Http\Controllers\Admin;

use App\Http\Middleware\MultiAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SocketController extends Controller {

  public function onClientConnect($request, $client) {
    return [$request, $request->user()->id];
  }

  /**
   * @param \App\Http\SocketBridge\SocketClient $client
   */
  public function startListen($client, ...$args) {
    $client->emit('hi', 'success message', 'successfully bridge', ...$args);
  }

}
