<?php

use App\Http\SocketBridge\SocketRoute;

SocketRoute::on('onConnect', 'SocketController@onClientConnect');
// SocketRoute::on('start-listen', 'SocketController@startListen');
