<?php
return [
  'default' => [
    'type'       => 'mysqli',
    'connection' => [
      'hostname'   => 'shiftboard-db-1',
      'port'       => '3306',
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