<?php

use App\Http\Controllers\Admin\SocketController;
use App\Http\SocketBridgeNew\RoomSocketRoute;
use App\Http\SocketBridgeNew\SocketRouteNew;

$roomName = 'admin';

RoomSocketRoute::group($roomName, function (RoomSocketRoute $room) {
  // $room->on('onConnect', 'Admin\SocketController@onClientConnect');
  $room->on('start-listen', 'Admin\SocketController@startListen');
  $room->on('onConnect', [SocketController::class, 'onClientConnect']);
});

// SocketRouteNew::on('onConnect', 'SocketController@onClientConnect');
// SocketRouteNew::on('start-listen', 'SocketController@startListen');
