<?php
return array(
    'driver'        => 'cookie',
    'cookie_name'   => 'fuelcid',
    'expiration_time' => 7200,
    'encrypt_cookie'  => true,
    'validate'      => array(),
    'auto_initialize' => true,
    'auto_start'    => true,
    'secure'        => false, // HTTPS環境では true に設定
    'httponly'      => true,
);