<?php

use App\Http\SocketBridge\RoomSocketRoute;
use App\Http\SocketBridge\SocketRoute;

$roomName = 'api';

RoomSocketRoute::group($roomName, function(RoomSocketRoute $room) {
  $room->on('onConnect', 'SocketController@onClientConnect');
  // $room->on('start-listen', 'SocketController@startListen');
});

// SocketRouteNew::on('onConnect', 'SocketController@onClientConnect');
// SocketRouteNew::on('start-listen', 'SocketController@startListen');
