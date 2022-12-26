<?php

use App\Http\SocketBridgeNew\RoomSocketRoute;
use App\Http\SocketBridgeNew\SocketRouteNew;

$roomName = 'api';

RoomSocketRoute::group($roomName, function(RoomSocketRoute $room) {
  $room->on('onConnect', 'SocketController@onClientConnect');
  // $room->on('start-listen', 'SocketController@startListen');
});

// SocketRouteNew::on('onConnect', 'SocketController@onClientConnect');
// SocketRouteNew::on('start-listen', 'SocketController@startListen');
