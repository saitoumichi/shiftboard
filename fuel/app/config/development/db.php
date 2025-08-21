<?php
return [
  "default" => [
    "type" => "mysqli",
    "connection" => [
      "hostname"   => "host.docker.internal",
      "port"       => "3306",
      "database"   => "shiftboard",
      "username"   => "app",
      "password"   => "AppPass!123",
      "persistent" => false,
    ],
    "charset" => "utf8mb4",
  ],
];
