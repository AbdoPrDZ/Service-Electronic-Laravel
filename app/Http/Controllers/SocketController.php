<?php

namespace App\Http\Controllers;

class SocketController extends Controller {

  public function onClientConnect($request, $client) {
    return [$request, $request->user()->id];
  }

}
