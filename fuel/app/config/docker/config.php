<?php
return [
  'base_url' => 'http://localhost:8081/',

  'cookie' => [
    'salt'      => 'change-this-to-any-random-string', // 空NG
    'http_only' => true,
    'secure'    => false,   // HTTPSなら true
    'path'      => '/',     // 重要
    'domain'    => null,    // 指定しない（localhostでOK）
    'samesite'  => 'Lax',
  ],

  'session' => [
    'driver'          => 'cookie',     // 今回は cookie でOK
    'cookie_name'     => 'fuelcid',
    'expiration_time' => 7200,         // 2時間
  ],
];