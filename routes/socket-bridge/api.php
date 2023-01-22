<?php

use App\Http\SocketBridge\RoomSocketRoute;
use App\Http\SocketBridge\SocketRoute;

$roomName = 'api';

RoomSocketRoute::group($roomName, function(RoomSocketRoute $room) {
  $room->on('onConnect', 'SocketController@onClientConnect');
});
