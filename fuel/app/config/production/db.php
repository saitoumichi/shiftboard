<?php
/**
 * Production環境用のデータベース設定
 * Renderの環境変数から接続情報を取得
 */

return array(
    'default' => array(
        'connection'  => array(
            'dsn'        => 'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
            'username'   => getenv('DB_USERNAME'),
            'password'   => getenv('DB_PASSWORD'),
        ),
        'type'           => 'pdo',
        'identifier'     => '`',
        'table_prefix'   => '',
        'charset'        => 'utf8mb4',
        'enable_cache'   => true,
        'profiling'      => false,
    ),
);

