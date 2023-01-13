<?php

return [
  'manager' => [
    'app_url' => 'http://127.0.0.1',
    'path' => '/socket.bridge-manager',
    'onConnect' => '/client/{clientId}/onConnect',
    'onDisconnect' => '/client/{clientId}/onDisconnect',
    'from_bridge' => '/client/{clientId}/on/{routeName}',
    'cache' => [
      'routes' =>'socket-bridge/routes',
    ],
    'rooms' => [
      'api' => [
        'routes_source' => 'routes/socket-bridge/api.php',
        'auth' => [
          'controller' => ['App\\Http\\Controllers\\SocketController', 'onSocketConnect'],
          'middleware' => 'socket.auth:sanctum',
          'auth_type' => 'token',
        ],
        'client_driver' => 'App\\Models\\User',
      ],
      'admin' => [
        'routes_source' => 'routes/socket-bridge/admin.php',
        'auth' => [
          'controller' => ['App\\Http\\Controllers\\Admin\\SocketController', 'onSocketConnect'],
          'middleware' => 'socket.auth:admin',
          'auth_type' => 'token',
        ],
        'client_driver' => 'App\\Models\\Admin',
      ],
    ],
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
    'from_manager' => '/:room/client/:clientId/emit/:routeName',
    'push_notification' => '/:room/client/:clientId/push_notification',
    'emit_notification' => '/:room/client/:clientId/emit_notification',
    'rooms' => [
      'api' => '/api/',
      'admin' => '/admin/',
    ]
  ],
  'connection_key' => 'df160237cd46322435083b0ef5afa1aff0fe87ad267fa728b031dc8bdd7606c3',
];
