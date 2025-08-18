<?php
return [
  'default' => [
    'type' => 'pdo',
    'connection' => [
      'dsn'      => 'mysql:host=db;dbname=shiftboard;port=3306;charset=utf8mb4',
      'username' => 'app',
      'password' => 'app_pass',
      'persistent' => false,
    ],
    'charset'      => 'utf8mb4',
    'profiling'    => true,
    'enable_cache' => false,
  ],
];
