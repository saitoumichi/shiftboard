<?php

/**
 * API Common Controller
 *
 * 各APIコントローラーで共通利用されるメソッドをまとめたクラス
 */
class Controller_Api_Common extends \Fuel\Core\Controller
{
    private static $pdo;

    /**
     * データベース接続を取得
     * 
     * @return PDO データベース接続
     * @throws Exception 接続エラー
     */
    public static function getDbConnection()
    {
        if (self::$pdo === null) {
            try {
                // 直接設定を使用（FuelPHPの設定読み込みに問題がある場合の回避策）
                $hostname = 'shiftboard-db-1';
                $port = '3306';
                $database = 'shiftboard';
                $username = 'app';
                $password = 'app_pass';
                
                $dsn = 'mysql:host=' . $hostname . 
                       ';port=' . $port .
                       ';dbname=' . $database . 
                       ';charset=utf8mb4';
                
                self::$pdo = new PDO(
                    $dsn,
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception('データベース接続エラー: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    /**
     * シフトデータをフォーマット
     * 
     * @param array $shift シフトデータ
     * @return array フォーマット済みシフトデータ
     */
    public static function formatShiftData($shift)
    {
        if (!$shift) {
            return null;
        }
        
        // 日付フォーマット
        if (isset($shift['shift_date'])) {
            $shift['shift_date'] = date('Y-m-d', strtotime($shift['shift_date']));
        }
        
        // 時間フォーマット（秒を除去）
        if (isset($shift['start_time'])) {
            $shift['start_time'] = substr($shift['start_time'], 0, 5);
        }
        if (isset($shift['end_time'])) {
            $shift['end_time'] = substr($shift['end_time'], 0, 5);
        }
        
        // 数値型の変換
        $shift['id'] = (int)$shift['id'];
        $shift['slot_count'] = (int)$shift['slot_count'];
        
        // available_slotsを動的に計算
        $slot_count = (int)$shift['slot_count'];
        $current_participants = 0;
        if (isset($shift['assigned_users']) && is_array($shift['assigned_users'])) {
            $current_participants = count($shift['assigned_users']);
        }
        $shift['available_slots'] = max(0, $slot_count - $current_participants);
        
        // 参加者データのフォーマット
        if (isset($shift['assigned_users']) && is_string($shift['assigned_users'])) {
            $assigned_users = json_decode($shift['assigned_users'], true);
            $shift['assigned_users'] = is_array($assigned_users) ? $assigned_users : [];
        } elseif (!isset($shift['assigned_users'])) {
            $shift['assigned_users'] = [];
        }
        
        // 各参加者データのフォーマット
        foreach ($shift['assigned_users'] as &$user) {
            if (isset($user['name'])) {
                $user['name'] = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
            }
            if (isset($user['status'])) {
                $user['status'] = htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $shift;
    }

    /**
     * シフト一覧データをフォーマット
     * 
     * @param array $shifts シフト一覧データ
     * @return array フォーマット済みシフト一覧データ
     */
    public static function formatShiftsList($shifts)
    {
        if (!is_array($shifts)) {
            return [];
        }
        
        $formatted = [];
        foreach ($shifts as $shift) {
            $formatted[] = self::formatShiftData($shift);
        }
        
        return $formatted;
    }

    /**
     * エラーレスポンスを生成
     * 
     * @param string $message エラーメッセージ
     * @param int $code HTTPステータスコード
     * @param mixed $data 追加データ
     * @return array エラーレスポンス
     */
    public static function errorResponse($message, $code = 500, $data = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }

    /**
     * 成功レスポンスを生成
     * 
     * @param mixed $data レスポンスデータ
     * @param string $message メッセージ
     * @param int $code HTTPステータスコード
     * @return array 成功レスポンス
     */
    public static function successResponse($data = null, $message = 'Success', $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'code' => $code
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }

    /**
     * バリデーションエラーレスポンスを生成
     * 
     * @param array $errors エラー配列
     * @return array バリデーションエラーレスポンス
     */
    public static function validationErrorResponse($errors)
    {
        return [
            'success' => false,
            'message' => 'バリデーションエラー',
            'errors' => $errors,
            'code' => 400
        ];
    }

    /**
     * 入力値をサニタイズ
     * 
     * @param mixed $value 入力値
     * @param string $type サニタイズタイプ
     * @return mixed サニタイズされた値
     */
    public static function sanitizeInput($value, $type = 'string')
    {
        if ($value === null || $value === '') {
            return $value;
        }
        
        switch ($type) {
            case 'int':
                return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);
            case 'string':
            default:
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * シフトIDのバリデーション
     * 
     * @param mixed $id シフトID
     * @return int|false 有効なIDまたはfalse
     */
    public static function validateShiftId($id)
    {
        $id = self::sanitizeInput($id, 'int');
        return ($id !== false && $id > 0) ? $id : false;
    }

    /**
     * ユーザーIDのバリデーション
     * 
     * @param mixed $id ユーザーID
     * @return int|false 有効なIDまたはfalse
     */
    public static function validateUserId($id)
    {
        $id = self::sanitizeInput($id, 'int');
        return ($id !== false && $id > 0) ? $id : false;
    }

    /**
     * 日付のバリデーション
     * 
     * @param string $date 日付文字列
     * @return string|false 有効な日付またはfalse
     */
    public static function validateDate($date)
    {
        $date = self::sanitizeInput($date);
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return ($d && $d->format('Y-m-d') === $date) ? $date : false;
    }

    /**
     * 時間のバリデーション
     * 
     * @param string $time 時間文字列
     * @return string|false 有効な時間またはfalse
     */
    public static function validateTime($time)
    {
        $time = self::sanitizeInput($time);
        return preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time) ? $time : false;
    }

    /**
     * シフトの存在確認
     * 
     * @param int $shiftId シフトID
     * @return bool 存在するかどうか
     */
    public static function shiftExists($shiftId)
    {
        try {
            $pdo = self::getDbConnection();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM shifts WHERE id = ?');
            $stmt->execute([$shiftId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * ユーザーのシフト参加状況を確認
     * 
     * @param int $userId ユーザーID
     * @param int $shiftId シフトID
     * @return bool 参加しているかどうか
     */
    public static function isUserParticipating($userId, $shiftId)
    {
        try {
            $pdo = self::getDbConnection();
            $stmt = $pdo->prepare('
                SELECT id FROM shift_assignments 
                WHERE member_id = ? AND shift_id = ? AND status != "cancelled"
            ');
            $stmt->execute([$userId, $shiftId]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * シフトの定員状況を確認
     * 
     * @param int $shiftId シフトID
     * @return array 定員情報
     */
    public static function getShiftCapacity($shiftId)
    {
        try {
            $pdo = self::getDbConnection();
            $stmt = $pdo->prepare('
                SELECT 
                    s.slot_count,
                    COUNT(sa.id) as current_participants
                FROM shifts s
                LEFT JOIN shift_assignments sa ON s.id = sa.shift_id AND sa.status != "cancelled"
                WHERE s.id = ?
                GROUP BY s.id, s.slot_count
            ');
            $stmt->execute([$shiftId]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return null;
            }
            
            return [
                'slot_count' => (int)$result['slot_count'],
                'current_participants' => (int)$result['current_participants'],
                'available_slots' => (int)$result['slot_count'] - (int)$result['current_participants']
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 認証済みユーザーIDを取得
     * 
     * @return int|null ユーザーID（認証されていない場合はnull）
     */
    public static function getCurrentUserId()
    {
        try {
            // セッションからユーザーIDを取得
            $user_id = \Fuel\Core\Session::get('user_id');
            if ($user_id) {
                return (int)$user_id;
            }
            
            // セッションにない場合は、認証ヘッダーから取得を試行
            $auth_header = \Fuel\Core\Input::headers('Authorization');
            if ($auth_header && preg_match('/Bearer\s+(.+)/', $auth_header, $matches)) {
                // JWTトークンやAPIキーからユーザーIDを取得する処理
                // ここでは簡易的にトークンからユーザーIDを抽出
                $token = $matches[1];
                // 実際の実装では、トークンを検証してユーザーIDを取得
                // デモ用にトークンが'user_1'の形式の場合を想定
                if (preg_match('/^user_(\d+)$/', $token, $token_matches)) {
                    return (int)$token_matches[1];
                }
            }
            
            // デバッグ用：認証が利用できない場合はデフォルトユーザーIDを返す
            // 本番環境では認証が必須の場合は例外を投げる
            if (\Fuel\Core\Config::get('environment') === 'development') {
                return 1; // デバッグ用のデフォルトユーザーID
            }
            
            return null;
            
        } catch (Exception $e) {
            // 認証エラーの場合はnullを返す
            return null;
        }
    }

    /**
     * 認証が必要な操作でユーザーIDを取得
     * 
     * @return int ユーザーID
     * @throws Exception 認証されていない場合
     */
    public static function requireCurrentUserId()
    {
        $user_id = self::getCurrentUserId();
        if (!$user_id) {
            throw new Exception('認証が必要です。ログインしてください。');
        }
        return $user_id;
    }
}
