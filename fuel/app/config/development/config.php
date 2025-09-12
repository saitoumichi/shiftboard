<?php
return [
    // 開発環境用の設定
    'log_threshold' => \Fuel::L_DEBUG, // デバッグレベルまでログを出力
    'profiling' => true, // 開発環境ではプロファイリングを有効
    'caching' => false, // 開発環境ではキャッシュを無効
    
    // エラー表示設定
    'errors' => [
        'notices' => true, // 開発環境ではnoticeも表示
        'throttle' => 100, // 開発環境ではより多くのエラーを表示
    ],
    
    // セキュリティ設定（開発環境では緩く）
    'security' => [
        'auto_filter_output' => false, // 開発環境では自動フィルタを無効
        'csrf_autoload' => false, // 開発環境ではCSRFを無効
    ],
];
