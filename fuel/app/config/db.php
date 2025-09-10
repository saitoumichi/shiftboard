<?php
return [
  'default' => [
    'type' => 'mysqli',
    'connection' => [
      'hostname'   => 'shiftboard-db-1',
      'port'       => '3306',
      'database'   => 'shiftboard',
      'username'   => 'app',
      'password'   => 'app_pass',
      'persistent' => false,
    ],
    'charset'      => 'utf8mb4',
    'profiling'    => true,
    'enable_cache' => false,
    'table_prefix' => '',
  ],
];
