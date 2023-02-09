<?php

namespace App\Http\Controllers;
use App\Http\SocketBridge\SocketClient;
use Cache;
use Log;

class SocketController extends Controller {

  public function onClientConnect($request, $client) {
    return [$request, $request->user()->id];
  }

  public function onClientDisconnect($request, $client) {
    if(!Cache::store('file')->has('api/users-listens')) {
      Cache::store('file')->set('api/users-listens', []);
    }
    $ids = Cache::store('file')->get('api/users-listens');
    if (!in_array($client->clientId, $ids)) {
      unset($ids[array_search($client->clientId, $ids)]);
      Cache::store('file')->set('api/users-listens', $ids);
    }
  }

  public function listenUser(SocketClient $client) {
    if(!Cache::store('file')->has('api/users-listens')) {
      Cache::store('file')->set('api/users-listens', []);
    }
    $ids = Cache::store('file')->get('api/users-listens');
    if (!in_array($client->clientId, $ids)) {
      $ids[] = $client->clientId;
      Cache::store('file')->set('api/users-listens', $ids);
    }
    $user = $client->user;
    $user->emitUpdates();
  }

}
