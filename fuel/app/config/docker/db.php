<?php
return [
  'default' => [
    'type'        => 'mysqli',
    'connection'  => [
      'hostname'   => 'db',        // ← compose のサービス名
      'port'       => '3306',      // ← コンテナ間は 3306
      'database'   => 'shiftboard',
      'username'   => 'app',
      'password'   => 'app_pass',
      'persistent' => false,
    ],
    'identifier'  => '`',
    'table_prefix'=> '',
    'charset'     => 'utf8mb4',
    'collation'   => 'utf8mb4_unicode_ci',
    'enable_cache'=> true,
    'profiling'   => false,
    'readonly'    => false,
  ],
];