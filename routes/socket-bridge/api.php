<?php

use App\Http\SocketBridge\RoomSocketRoute;
use App\Http\SocketBridge\SocketRoute;

$roomName = 'api';

RoomSocketRoute::group($roomName, function(RoomSocketRoute $room) {
  $room->on('onConnect', 'SocketController@onClientConnect');
  $room->on('onDisconnect', 'SocketController@onClientDisconnect');
  $room->on('listenUser', 'SocketController@listenUser');
});
