<?php
return [
    // 基本設定
    'base_url' => '/',
    'index_file' => false,
    'controller_prefix' => 'Controller_',
    
    // 言語・ロケール設定
    'language' => 'ja',
    'locale' => ['ja_JP.UTF-8', 'C.UTF-8', 'C'],
    'encoding' => 'UTF-8',
    
    // タイムゾーン設定
    'default_timezone' => 'Asia/Tokyo',
    'server_gmt_offset' => 32400, // JST = UTC+9時間 = 32400秒
    
    // プロファイリング・キャッシュ
    'profiling' => false,
    'caching' => false,
    'cache_dir' => APPPATH.'cache/',
    'cache_lifetime' => 3600,
    
    // エラー処理
    'errors' => [
        'continue_on' => [],
        'throttle' => 10,
        'notices' => true,
    ],
    
    // ログ設定
    'log_threshold' => \Fuel::L_WARNING,
    'log_path' => APPPATH.'logs/',
    'log_date_format' => 'Y-m-d H:i:s',
    
    // セキュリティ設定
    'security' => [
        'csrf_autoload' => false,
        'csrf_token_key' => 'fuel_csrf_token',
        'csrf_expiration' => 7200, // 2時間
        'token_salt' => 'shiftboard_salt_2024_secure_token',
        'allow_x_headers' => false,
        'uri_filter' => ['htmlentities'],
        'input_filter' => [],
        'output_filter' => [],
        'auto_filter_output' => true,
        'whitelisted_classes' => [
            'stdClass',
            'Fuel\\Core\\View',
            'Fuel\\Core\\ViewModel',
            'Closure'
        ],
    ],
    
    // クッキー設定
    'cookie' => [
        'expiration' => 0,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => false,
    ],
    
    // パッケージ・モジュール設定
    'module_paths' => [],
    'always_load' => [
        'packages' => [],
        'modules' => [],
        'classes' => [],
        'config' => [],
        'language' => [],
    ],
];
