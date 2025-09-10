<?php
return [
  'default' => [
    'type' => 'mysqli',
    'connection' => [
      // コンテナ間の接続はサービス名 "db" と 3306 を使う
      'hostname'   => 'db',
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
