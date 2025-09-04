<?php

/**
 * API Common Controller
 *
 * 各APIコントローラーで共通利用されるメソッドをまとめたクラス
 */
class Controller_Api_Common extends \Fuel\Core\Controller
{
    private static $pdo;

    public static function getDbConnection()
    {
        if (self::$pdo === null) {
            $config = \Fuel\Core\Config::load('db', true);
            $dsn = 'mysql:host=' . $config['default']['connection']['hostname'] . ';dbname=' . $config['default']['connection']['database'] . ';charset=utf8mb4';
            self::$pdo = new PDO($dsn, $config['default']['connection']['username'], $config['default']['connection']['password']);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    public static function formatShiftData($shift)
    {
        $assigned_count = count($shift['assigned_users'] ?? []);
        $shift['available_slots'] = max(0, $shift['slot_count'] - $assigned_count);
        return $shift;
    }

    public static function successResponse($data, $message = '')
    {
        return json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ]);
    }

    public static function errorResponse($message = '', $status = 500)
    {
        return json_encode([
            'success' => false,
            'message' => $message,
        ]);
    }
    
    public static function validationErrorResponse($errors)
    {
        return json_encode([
            'success' => false,
            'message' => 'バリデーションエラー',
            'errors' => $errors,
        ]);
    }

    public static function validateShiftId($id)
    {
        return filter_var($id, FILTER_VALIDATE_INT) ? (int)$id : false;
    }

    public static function shiftExists($id)
    {
        $pdo = self::getDbConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM shifts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public static function isUserParticipating($userId, $shiftId)
    {
        $pdo = self::getDbConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM shift_assignments WHERE member_id = ? AND shift_id = ? AND status != 'cancelled'");
        $stmt->execute([$userId, $shiftId]);
        return $stmt->fetchColumn() > 0;
    }

    public static function getShiftCapacity($id)
    {
        $pdo = self::getDbConnection();
        $stmt = $pdo->prepare("SELECT slot_count FROM shifts WHERE id = ?");
        $stmt->execute([$id]);
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$shift) return null;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM shift_assignments WHERE shift_id = ? AND status != 'cancelled'");
        $stmt->execute([$id]);
        $current_participants = $stmt->fetchColumn();

        return [
            'slot_count' => $shift['slot_count'],
            'current_participants' => $current_participants,
            'available_slots' => max(0, $shift['slot_count'] - $current_participants)
        ];
    }
    
    public static function requireCurrentUserId()
    {
        // 実際の認証システムからユーザーIDを取得するロジックをここに実装する
        // 現時点ではデバッグ用にハードコード
        return 1;
    }

    public static function sanitizeInput($input, $type = 'string')
    {
        if ($type === 'int') {
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date ? $date : false;
    }

    public static function validateTime($time)
    {
        return preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time) ? $time : false;
    }
}