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
    'collation'    => 'utf8mb4_unicode_ci',
    'profiling'    => false,
    'caching'      => false,
  ],
];