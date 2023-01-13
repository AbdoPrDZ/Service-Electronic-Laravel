<?php
return [

  'default' => 'public',

  'clients' => [
    'admin' => [
      'guard' => 'admin',
      'auth' => true,
      'access_disks' => ['admin', 'public', 'api'],
    ],
    'api' => [
      'guard' => 'sanctum',
      'auth' => true,
      'access_disks' => ['api', 'public'],
    ],
    'public' => [
      'auth' => false,
      'access_disks' => ['public'],
    ],
  ]
];
