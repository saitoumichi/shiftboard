<?php
return [
    // 本番環境用の設定
    'log_threshold' => \Fuel::L_ERROR, // 本番環境ではエラーレベルのみログ出力
    'profiling' => false, // 本番環境ではプロファイリングを無効
    'caching' => true, // 本番環境ではキャッシュを有効
    
    // エラー表示設定
    'errors' => [
        'notices' => false, // 本番環境ではnoticeは非表示
        'throttle' => 10, // 本番環境ではエラー数を制限
    ],
    
    // セキュリティ設定（本番環境では厳しく）
    'security' => [
        'auto_filter_output' => true, // 本番環境では自動フィルタを有効
        'csrf_autoload' => true, // 本番環境ではCSRFを有効
        'csrf_expiration' => 3600, // CSRFトークンの有効期限を1時間に短縮
    ],
    
    // キャッシュ設定
    'cache_lifetime' => 7200, // 本番環境ではキャッシュ期間を延長
];
