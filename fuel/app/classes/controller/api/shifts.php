<?php

/**
 * API Shifts Controller
 * シフト管理用のAPIコントローラー
 */
class Controller_Api_Shifts extends \Fuel\Core\Controller
{
    /**
     * レスポンスを返す
     * 
     * @param mixed $data レスポンスデータ
     * @param int $status HTTPステータスコード
     * @return \Fuel\Core\Response
     */
    protected function response($data, $status = 200)
    {
        $response = new \Fuel\Core\Response();
        $response->set_status($status);
        $response->set_header('Content-Type', 'application/json; charset=utf-8');
        $response->body = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        return $response;
    }

    /**
     * シフト一覧取得 / シフト作成
     */
    public function action_index()
    {
        try {
            if (\Fuel\Core\Input::method() === 'POST') {
                return $this->action_create();
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // シフト一覧を取得
            $stmt = $pdo->prepare("SELECT * FROM shifts ORDER BY shift_date ASC, start_time ASC");
            $stmt->execute();
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = array();
            foreach ($shifts as $shift) {
                // 参加者情報を取得
                $stmt = $pdo->prepare("
                    SELECT m.name, sa.status 
                    FROM shift_assignments sa 
                    JOIN members m ON sa.member_id = m.id 
                    WHERE sa.shift_id = ? AND sa.status != 'cancelled'
                ");
                $stmt->execute([$shift['id']]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'name' => $assignment['name'],
                        'status' => $assignment['status']
                    );
                }

                // シフトデータに参加者情報を追加
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
            // 入力データを取得・サニタイズ
            $shift_date = Controller_Api_Common::validateDate(\Fuel\Core\Input::post('shift_date'));
            $start_time = Controller_Api_Common::validateTime(\Fuel\Core\Input::post('start_time'));
            $end_time = Controller_Api_Common::validateTime(\Fuel\Core\Input::post('end_time'));
            $note = Controller_Api_Common::sanitizeInput(\Fuel\Core\Input::post('note'));
            $slot_count = Controller_Api_Common::sanitizeInput(\Fuel\Core\Input::post('slot_count', 1), 'int');

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
            if (!$slot_count || $slot_count < 1) {
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

            // シフトを作成
            $stmt = $pdo->prepare("
                INSERT INTO shifts (shift_date, start_time, end_time, note, slot_count, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$shift_date, $start_time, $end_time, $note, $slot_count]);

            $shift_id = $pdo->lastInsertId();

            // 作成されたシフトの情報を取得
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$shift_id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

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

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // シフト詳細を取得
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$validId]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($shift) {
                // 参加者情報を取得
                $stmt = $pdo->prepare("
                    SELECT m.name, sa.status 
                    FROM shift_assignments sa 
                    JOIN members m ON sa.member_id = m.id 
                    WHERE sa.shift_id = ? AND sa.status != 'cancelled'
                ");
                $stmt->execute([$validId]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'name' => $assignment['name'],
                        'status' => $assignment['status']
                    );
                }

                // シフトデータに参加者情報を追加
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
                WHERE member_id = ? AND shift_id = ?
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
                    INSERT INTO shift_assignments (member_id, shift_id, status, created_at) 
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
                WHERE member_id = ? AND shift_id = ? AND status != 'cancelled'
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
            $shift_date = Controller_Api_Common::validateDate(\Fuel\Core\Input::post('shift_date'));
            $start_time = Controller_Api_Common::validateTime(\Fuel\Core\Input::post('start_time'));
            $end_time = Controller_Api_Common::validateTime(\Fuel\Core\Input::post('end_time'));
            $note = Controller_Api_Common::sanitizeInput(\Fuel\Core\Input::post('note'));
            $slot_count = Controller_Api_Common::sanitizeInput(\Fuel\Core\Input::post('slot_count'), 'int');

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
            if (!$slot_count || $slot_count < 1) {
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
            if ($slot_count < $capacity['current_participants']) {
                return $this->response(Controller_Api_Common::errorResponse('定員を現在の参加者数より少なくすることはできません', 400), 400);
            }

            // シフトを更新
            $stmt = $pdo->prepare("
                UPDATE shifts 
                SET shift_date = ?, start_time = ?, end_time = ?, note = ?, slot_count = ?, 
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$shift_date, $start_time, $end_time, $note, $slot_count, $id]);

            // 更新されたシフトの情報を取得
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($shift) {
                // 参加者情報を取得
                $stmt = $pdo->prepare("
                    SELECT m.name, sa.status 
                    FROM shift_assignments sa 
                    JOIN members m ON sa.member_id = m.id 
                    WHERE sa.shift_id = ? AND sa.status != 'cancelled'
                ");
                $stmt->execute([$id]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'name' => $assignment['name'],
                        'status' => $assignment['status']
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