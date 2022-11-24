<?php

return [
  'manager' => [
    'app_url' => 'http://localhost',
    'path' => '/socket.bridge-manager',
    // 'onConnect' => '/client/{client}/{clientId}/onConnect',
    'onConnect' => '/client/{clientId}/onConnect',
    // 'onDisconnect' => '/client/{client}/{clientId}/onDisconnect',
    'onDisconnect' => '/client/{clientId}/onDisconnect',
    // 'from_bridge' => '/client/{client}/{clientId}/on/{routeName}',
    'from_bridge' => '/client/{clientId}/on/{routeName}',
    'cache' => [
      'routes' =>'socket-bridge/routes',
    ],
    // 'rooms' => [
    //   'api' => [
        'routes_source' => 'routes/socket.php',
        'auth' => [
          'controller' => ['\\App\\Http\\Controllers\\SocketController', 'onSocketConnect'],
          'middleware' => 'multi.auth:sanctum',
        ],
        'client_driver' => '\\App\\Models\\User',
      // ]
    // ],
  ],
  'bridge' => [
    'protocol' => 'http',
    'host' => 'localhost',
    'port' => '3000',
    'socket' => [
      'protocol' => 'http',
      'host' => '0.0.0.0',
      'port' => '8000',
    ],
    'path' => '/socket.bridge-server',
    'socket_path' => '',
    // 'from_manager' => '/client/:client/:clientId/emit/:routeName',
    'from_manager' => '/client/:clientId/emit/:routeName',
    // 'push_notification' => '/client/:client/:clientId/push_notification',
    'push_notification' => '/client/:client/push_notification',
  ],
  'connection_key' => 'df160237cd46322435083b0ef5afa1aff0fe87ad267fa728b031dc8bdd7606c3',
];
