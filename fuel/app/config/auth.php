<?php
/**
 * 認証設定ファイル
 * 
 * このファイルでは認証に関する設定を定義します。
 * 本番環境では適切な認証システムを実装してください。
 */

return array(
    // 認証方式の設定
    'driver' => 'session', // 'session', 'jwt', 'api_key' など
    
    // セッション認証の設定
    'session' => array(
        'user_id_key' => 'user_id',
        'login_redirect' => '/login',
        'logout_redirect' => '/',
    ),
    
    // JWT認証の設定（将来の実装用）
    'jwt' => array(
        'secret_key' => 'your-secret-key-here',
        'algorithm' => 'HS256',
        'expiration' => 3600, // 1時間
    ),
    
    // API認証の設定（将来の実装用）
    'api' => array(
        'header_name' => 'Authorization',
        'token_prefix' => 'Bearer',
    ),
    
    // デバッグ用設定
    'debug' => array(
        'default_user_id' => 1, // 開発環境でのデフォルトユーザーID
        'enable_fallback' => true, // 認証失敗時のフォールバックを有効にするか
    ),
);
