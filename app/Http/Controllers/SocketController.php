<?php

namespace App\Http\Controllers;

use App\Http\Middleware\MultiAuth;
use Illuminate\Http\Request;

class SocketController extends Controller {

  public function onClientConnect($request, $client) {
    return [$request, $request->user()->id];
  }

  // /**
  //  * @param \App\Http\SocketBridge\SocketClient $client
  //  */
  // public function startListen($client, ...$args) {
  //   $client->emit('hi', ...$args);
  // }

}
