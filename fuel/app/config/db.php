<?php
return [
    'default' => [
        'type' => 'mysqli',
        'connection' => [
            'hostname' => 'shiftboard-db-1',
            'port' => '3306',
            'database' => 'shiftboard',
            'username' => 'app',
            'password' => 'app_pass',
            'persistent' => false,
        ],
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'profiling' => true,
        'enable_cache' => false,
        'table_prefix' => '',
        'identifier' => '`',
        'save_queries' => true,
    ],
];
