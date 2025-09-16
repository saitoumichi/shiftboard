<?php
return [
  'default' => [
    'type'       => 'mysqli',
    'connection' => [
      'hostname'   => '127.0.0.1',
      'port'       => '13306',
      'database'   => 'shiftboard',
      'username'   => 'app',
      'password'   => 'app_pass',
      'persistent' => false,
    ],
    'table_prefix' => '',
    'charset'      => 'utf8mb4',
    'enable_cache' => true,
    'profiling'    => false,
  ],
];