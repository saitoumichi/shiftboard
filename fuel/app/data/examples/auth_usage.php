<?php
/**
 * 認証システムの使用例
 * 
 * このファイルは認証システムの使用方法を示す例です。
 * 実際のプロジェクトでは適切な認証システムを実装してください。
 */

// 1. セッション認証の例
// ログイン時にセッションにユーザーIDを保存
\Fuel\Core\Session::set('user_id', 123);

// 2. API認証の例（JWTトークン）
// フロントエンドからAPIリクエストを送信する際のヘッダー例
/*
fetch('/api/shifts/1/join', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer user_123',
        'Content-Type': 'application/json'
    }
});
*/

// 3. 認証が必要なAPIエンドポイントでの使用例
class ExampleController extends \Fuel\Core\Controller
{
    public function action_protected()
    {
        try {
            // 認証済みユーザーIDを取得
            $user_id = Controller_Api_Common::requireCurrentUserId();
            
            // 認証されたユーザーのみがアクセス可能
            return $this->response([
                'success' => true,
                'user_id' => $user_id,
                'message' => '認証されたユーザーです'
            ]);
            
        } catch (Exception $e) {
            return $this->response([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}

// 4. 認証状態の確認例
$user_id = Controller_Api_Common::getCurrentUserId();
if ($user_id) {
    echo "ログイン済みユーザーID: " . $user_id;
} else {
    echo "未ログイン";
}

// 5. 本番環境での実装例
/*
本番環境では以下のような実装を推奨します：

1. セッション認証：
   - ログイン時にセッションにユーザー情報を保存
   - セッションの有効期限を適切に設定
   - CSRFトークンを使用

2. JWT認証：
   - トークンの署名検証
   - 有効期限の確認
   - リフレッシュトークンの実装

3. API認証：
   - APIキーの管理
   - レート制限の実装
   - ログの記録
*/
