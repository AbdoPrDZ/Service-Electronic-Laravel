<?php

use App\Http\Controllers\Admin\SocketController;
use App\Http\SocketBridge\RoomSocketRoute;
use App\Http\SocketBridge\SocketRoute;

$roomName = 'admin';

RoomSocketRoute::group($roomName, function (RoomSocketRoute $room) {
  $room->on('news', 'Admin\SocketController@news');
  $room->on('read-news', 'Admin\SocketController@readNews');
  $room->on('onConnect', [SocketController::class, 'onClientConnect']);
});
