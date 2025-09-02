<?php
return [
  "default" => [
    "type" => "mysqli",
    "connection" => [
      "hostname"   => "127.0.0.1",
      "port"       => "13306",
      "database"   => "shiftboard",
      "username"   => "app",
      "password"   => "app_pass",
      "persistent" => false,
    ],
    "charset" => "utf8mb4",
    "profiling" => true,
    "enable_cache" => false,
    "table_prefix" => "",
  ],
];
