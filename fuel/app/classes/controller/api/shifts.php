<?php

/**
 * API Shifts Controller
 * シフト管理用のAPIコントローラー
 */
class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';
    /**
     * レスポンスを返す
     * 
     * @param mixed $data レスポンスデータ
     * @param int $status HTTPステータスコード
     * @return \Fuel\Core\Response
     */

    /**
     * シフト一覧取得 / シフト作成
     */

     public function get_index()
    {
        $rows = \Fuel\Core\DB::query("
            SELECT s.id, s.created_by, s.shift_date, s.start_time, s.end_time,
                   s.recruit_count, s.free_text, s.created_at, s.updated_at,
                   COUNT(CASE WHEN sa.status != 'cancelled' THEN sa.user_id END) AS joined_count
            FROM shifts s
            LEFT JOIN shift_assignments sa ON sa.shift_id = s.id
            GROUP BY s.id, s.created_by, s.shift_date, s.start_time, s.end_time, 
                     s.recruit_count, s.free_text, s.created_at, s.updated_at
            ORDER BY s.shift_date, s.start_time
        ")->execute()->as_array();

        return $this->response(['items' => $rows]);
    }

    public function action_index()
    {
        try {
            if (\Fuel\Core\Input::method() === 'POST') {
                return $this->action_create();
            }

            // FuelPHPのDBクラスを使用してシフト一覧を取得
            $shifts = DB::select()->from('shifts')
                ->order_by('shift_date', 'ASC')
                ->order_by('start_time', 'ASC')
                ->execute()
                ->as_array();

            $data = array();
            foreach ($shifts as $shift) {
                // 割り当て者情報を取得（shift_assignmentsテーブルを使用）
                $assignments = DB::select('u.name', 'sa.status', 'sa.self_word')
                    ->from('shift_assignments', 'sa')
                    ->join('users', 'INNER')
                    ->on('sa.user_id', '=', 'u.id')
                    ->where('sa.shift_id', $shift['id'])
                    ->where('sa.status', '!=', 'cancelled')
                    ->execute()
                    ->as_array();

                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'name' => $assignment['name'],
                        'status' => $assignment['status'],
                        'self_word' => $assignment['self_word']
                    );
                }

                // シフトデータに割り当て者情報を追加
                $shift['assigned_users'] = $assigned_users;
                
                // 共通関数でフォーマット
                $data[] = Controller_Api_Common::formatShiftData($shift);
            }

            return $this->response(Controller_Api_Common::successResponse($data, 'シフト一覧を取得しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト一覧の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト作成
     */
    public function action_create()
    {
        try {
            // JSONデータを取得
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // フォールバック：POSTデータも確認
            if (empty($data) && !empty($_POST)) {
                $data = $_POST;
            }
            
            // 入力データを取得・サニタイズ
            $created_by = Controller_Api_Common::sanitizeInput($data['created_by'] ?? 1, 'int'); // デフォルトは1
            $shift_date = Controller_Api_Common::validateDate($data['shift_date'] ?? '');
            $start_time = Controller_Api_Common::validateTime($data['start_time'] ?? '');
            $end_time = Controller_Api_Common::validateTime($data['end_time'] ?? '');
            $free_text = Controller_Api_Common::sanitizeInput($data['free_text'] ?? '');
            $recruit_count = Controller_Api_Common::sanitizeInput($data['recruit_count'] ?? 1, 'int');

            // バリデーション
            $errors = array();
            if (!$created_by || $created_by < 1) {
                $errors[] = '作成者IDは1以上の整数である必要があります';
            }
            if (!$shift_date) {
                $errors[] = '有効なシフト日付を入力してください（YYYY-MM-DD形式）';
            }
            if (!$start_time) {
                $errors[] = '有効な開始時刻を入力してください（HH:MM形式）';
            }
            if (!$end_time) {
                $errors[] = '有効な終了時刻を入力してください（HH:MM形式）';
            }
            if (!$recruit_count || $recruit_count < 1) {
                $errors[] = '募集人数は1以上の整数である必要があります';
            }
            
            // 時間の論理チェック（両方の時刻が有効な場合のみ実行）
            if ($start_time !== false && $end_time !== false && 
                !empty($start_time) && !empty($end_time) && 
                is_string($start_time) && is_string($end_time)) {
                
                // 時刻の比較（文字列として比較可能）
                if ($start_time >= $end_time) {
                    $errors[] = '終了時刻は開始時刻より後である必要があります';
                }
            }

            if (!empty($errors)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($errors), 400);
            }

            // FuelPHPのDBクラスを使用してシフトを作成
            $shift_id = DB::insert('shifts')
                ->set([
                    'created_by' => $created_by,
                    'shift_date' => $shift_date,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'free_text' => $free_text,
                    'recruit_count' => $recruit_count,
                    'created_at' => DB::expr('CURRENT_TIMESTAMP')
                ])
                ->execute();

            // 作成されたシフトの情報を取得
            $shift = DB::select()->from('shifts')
                ->where('id', $shift_id[0])
                ->execute()
                ->current();

            if ($shift) {
                $shift['assigned_users'] = array();
                $formatted_shift = Controller_Api_Common::formatShiftData($shift);
                
                return $this->response(Controller_Api_Common::successResponse($formatted_shift, 'シフトを作成しました'));
            } else {
                return $this->response(Controller_Api_Common::errorResponse('シフトの作成に失敗しました', 500), 500);
            }

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの作成に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト詳細取得
     */
    public function action_show($id)
    {
        try {
            // シフトIDのバリデーション
            $validId = Controller_Api_Common::validateShiftId($id);
            if (!$validId) {
                return $this->response(Controller_Api_Common::errorResponse('無効なシフトIDです', 400), 400);
            }
            
            // シフトの存在確認
            if (!Controller_Api_Common::shiftExists($validId)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            // FuelPHPのDBクラスを使用してシフト詳細を取得
            $shift = DB::select()->from('shifts')
                ->where('id', $validId)
                ->execute()
                ->current();

            if ($shift) {
                // 割り当て者情報を取得（shift_assignmentsテーブルを使用）
                $assignments = DB::select('u.name', 'sa.status', 'sa.self_word', 'sa.user_id')
                    ->from('shift_assignments', 'sa')
                    ->join('users', 'INNER')
                    ->on('sa.user_id', '=', 'u.id')
                    ->where('sa.shift_id', $validId)
                    ->where('sa.status', '!=', 'cancelled')
                    ->execute()
                    ->as_array();

                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'user_id' => $assignment['user_id'],
                        'name' => $assignment['name'],
                        'status' => $assignment['status'],
                        'self_word' => $assignment['self_word']
                    );
                }

                // シフトデータに割り当て者情報を追加
                $shift['assigned_users'] = $assigned_users;
                
                // 共通関数でフォーマット
                $formatted_shift = Controller_Api_Common::formatShiftData($shift);

                return $this->response(Controller_Api_Common::successResponse($formatted_shift, 'シフト詳細を取得しました'));
            } else {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト参加
     */
    public function action_join($id)
    {
        try {
            // シフトIDのバリデーション
            $validId = Controller_Api_Common::validateShiftId($id);
            if (!$validId) {
                return $this->response(Controller_Api_Common::errorResponse('無効なシフトIDです', 400), 400);
            }

            // シフトの存在確認
            if (!Controller_Api_Common::shiftExists($validId)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            // 認証済みユーザーIDを取得
            try {
                $user_id = Controller_Api_Common::requireCurrentUserId();
            } catch (Exception $e) {
                return $this->response(Controller_Api_Common::errorResponse($e->getMessage(), 401), 401);
            }

            // 既に参加しているかチェック
            if (Controller_Api_Common::isUserParticipating($user_id, $validId)) {
                return $this->response(Controller_Api_Common::errorResponse('既に参加しています', 409), 409);
            }

            // 定員確認
            $capacity = Controller_Api_Common::getShiftCapacity($validId);
            if (!$capacity || $capacity['available_slots'] <= 0) {
                return $this->response(Controller_Api_Common::errorResponse('定員に達しています', 409), 409);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // 既存のレコードがあるかチェック（cancelledも含む）
            $stmt = $pdo->prepare("
                SELECT id, status FROM shift_assignments 
                WHERE user_id = ? AND shift_id = ?
            ");
            $stmt->execute([$user_id, $validId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // 既存のレコードがある場合は更新
                $stmt = $pdo->prepare("
                    UPDATE shift_assignments 
                    SET status = 'confirmed'
                    WHERE id = ?
                ");
                $stmt->execute([$existing['id']]);
            } else {
                // 既存のレコードがない場合は新規作成
                $stmt = $pdo->prepare("
                    INSERT INTO shift_assignments (user_id, shift_id, status, created_at) 
                    VALUES (?, ?, 'confirmed', NOW())
                ");
                $stmt->execute([$user_id, $validId]);
            }

            return $this->response(Controller_Api_Common::successResponse(null, 'シフトに参加しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト参加に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト参加取消
     */
    public function action_cancel($id)
    {
        try {
            // シフトIDのバリデーション
            $validId = Controller_Api_Common::validateShiftId($id);
            if (!$validId) {
                return $this->response(Controller_Api_Common::errorResponse('無効なシフトIDです', 400), 400);
            }

            // シフトの存在確認
            if (!Controller_Api_Common::shiftExists($validId)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            // 認証済みユーザーIDを取得
            try {
                $user_id = Controller_Api_Common::requireCurrentUserId();
            } catch (Exception $e) {
                return $this->response(Controller_Api_Common::errorResponse($e->getMessage(), 401), 401);
            }

            // 参加しているかチェック
            if (!Controller_Api_Common::isUserParticipating($user_id, $validId)) {
                return $this->response(Controller_Api_Common::errorResponse('参加していません', 409), 409);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // 参加取消（statusを'cancelled'に更新）
            $stmt = $pdo->prepare("
                UPDATE shift_assignments 
                SET status = 'cancelled'
                WHERE user_id = ? AND shift_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$user_id, $validId]);

            return $this->response(Controller_Api_Common::successResponse(null, 'シフト参加を取消しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト参加取消に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト更新
     */
    public function action_update($id)
    {
        try {
            // シフトの存在確認
            if (!Controller_Api_Common::shiftExists($id)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            // 入力データを取得・サニタイズ
            $created_by = Controller_Api_Common::sanitizeInput(\Fuel\Core\Input::post('created_by'), 'int');
            $shift_date = Controller_Api_Common::validateDate(\Fuel\Core\Input::post('shift_date'));
            $start_time = Controller_Api_Common::validateTime(\Fuel\Core\Input::post('start_time'));
            $end_time = Controller_Api_Common::validateTime(\Fuel\Core\Input::post('end_time'));
            $free_text = Controller_Api_Common::sanitizeInput(\Fuel\Core\Input::post('free_text'));
            $recruit_count = Controller_Api_Common::sanitizeInput(\Fuel\Core\Input::post('recruit_count'), 'int');

            // バリデーション
            $errors = array();
            if (!$shift_date) {
                $errors[] = '有効なシフト日付を入力してください（YYYY-MM-DD形式）';
            }
            if (!$start_time) {
                $errors[] = '有効な開始時刻を入力してください（HH:MM形式）';
            }
            if (!$end_time) {
                $errors[] = '有効な終了時刻を入力してください（HH:MM形式）';
            }
            if (!$recruit_count || $recruit_count < 1) {
                $errors[] = '募集人数は1以上の整数である必要があります';
            }
            
            // 時間の論理チェック（両方の時刻が有効な場合のみ実行）
            if ($start_time !== false && $end_time !== false && 
                !empty($start_time) && !empty($end_time) && 
                is_string($start_time) && is_string($end_time)) {
                
                // 時刻の比較（文字列として比較可能）
                if ($start_time >= $end_time) {
                    $errors[] = '終了時刻は開始時刻より後である必要があります';
                }
            }

            if (!empty($errors)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($errors), 400);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // 現在の参加者数を取得
            $capacity = Controller_Api_Common::getShiftCapacity($id);
            if (!$capacity) {
                return $this->response(Controller_Api_Common::errorResponse('シフト情報の取得に失敗しました', 500), 500);
            }

            // 新しい定員が現在の参加者数より少ない場合はエラー
            if ($recruit_count < $capacity['current_participants']) {
                return $this->response(Controller_Api_Common::errorResponse('定員を現在の参加者数より少なくすることはできません', 400), 400);
            }

            // シフトを更新
            $stmt = $pdo->prepare("
                UPDATE shifts 
                SET created_by = ?, shift_date = ?, start_time = ?, end_time = ?, free_text = ?, recruit_count = ?, 
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$created_by, $shift_date, $start_time, $end_time, $free_text, $recruit_count, $id]);

            // 更新されたシフトの情報を取得
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($shift) {
                // 参加者情報を取得
                $stmt = $pdo->prepare("
                    SELECT u.name, sa.status, sa.self_word, sa.user_id
                    FROM shift_assignments sa 
                    JOIN users u ON sa.user_id = u.id 
                    WHERE sa.shift_id = ? AND sa.status != 'cancelled'
                ");
                $stmt->execute([$id]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'user_id' => $assignment['user_id'],
                        'name' => $assignment['name'],
                        'status' => $assignment['status'],
                        'self_word' => $assignment['self_word']
                    );
                }

                $shift['assigned_users'] = $assigned_users;
                $formatted_shift = Controller_Api_Common::formatShiftData($shift);
                
                return $this->response(Controller_Api_Common::successResponse($formatted_shift, 'シフトを更新しました'));
            } else {
                return $this->response(Controller_Api_Common::errorResponse('シフトの更新に失敗しました', 500), 500);
            }

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの更新に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト削除
     */
    public function action_delete($id)
    {
        try {
            // シフトの存在確認
            if (!Controller_Api_Common::shiftExists($id)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // 参加者情報を削除
            $stmt = $pdo->prepare("DELETE FROM shift_assignments WHERE shift_id = ?");
            $stmt->execute([$id]);

            // シフトを削除
            $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
            $stmt->execute([$id]);

            return $this->response(Controller_Api_Common::successResponse(null, 'シフトを削除しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの削除に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
}