<?php

/**
 * API Shifts Controller
 * 
 * シフト管理用のAPIコントローラー
 */
class Controller_Api_Shifts extends \Fuel\Core\Controller
{
    /**
     * シフト一覧取得 / シフト作成
     */
    public function action_index()
    {
        // POSTリクエストの場合はシフト作成
        if (\Fuel\Core\Input::method() === 'POST') {
            return $this->action_create();
        }
        
        try {
            // シフト一覧を取得（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            $stmt = $pdo->query("SELECT * FROM shifts ORDER BY shift_date ASC, start_time ASC");
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = array();
            foreach ($shifts as $shift) {
                // 各シフトの割り当て情報を取得
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

                $data[] = array(
                    'id' => $shift['id'],
                    'shift_date' => $shift['shift_date'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'note' => $shift['note'],
                    'slot_count' => $shift['slot_count'],
                    'available_slots' => max(0, $shift['slot_count'] - count($assigned_users)),
                    'assigned_users' => $assigned_users,
                    'created_at' => $shift['created_at'],
                    'updated_at' => $shift['updated_at']
                );
            }

            return $this->response(array(
                'success' => true,
                'data' => $data,
                'message' => 'シフト一覧を取得しました'
            ));

        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフト一覧の取得に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * シフト詳細取得
     */
    public function action_show($id = null)
    {
        if (!$id) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフトIDが指定されていません'
            ), 400);
        }

        try {
            // シフト詳細を取得（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$shift) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトが見つかりません'
                ), 404);
            }

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
                    'id' => 1, // 仮のユーザーID
                    'name' => $assignment['name'],
                    'status' => $assignment['status']
                );
            }

            $data = array(
                'id' => $shift['id'],
                'shift_date' => $shift['shift_date'],
                'start_time' => $shift['start_time'],
                'end_time' => $shift['end_time'],
                'note' => $shift['note'],
                'slot_count' => $shift['slot_count'],
                'available_slots' => max(0, $shift['slot_count'] - count($assigned_users)),
                'assigned_users' => $assigned_users,
                'created_at' => $shift['created_at'],
                'updated_at' => $shift['updated_at']
            );

            return $this->response(array(
                'success' => true,
                'data' => $data,
                'message' => 'シフト詳細を取得しました'
            ));

        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフト詳細の取得に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * シフト作成
     */
    public function action_create()
    {
        try {
            $shift_date = \Fuel\Core\Input::post('shift_date');
            $start_time = \Fuel\Core\Input::post('start_time');
            $end_time = \Fuel\Core\Input::post('end_time');
            $slot_count = \Fuel\Core\Input::post('slot_count', 1);
            $note = \Fuel\Core\Input::post('note', '');
            
            if (!$shift_date || !$start_time || !$end_time) {
                return $this->response(array(
                    'success' => false,
                    'message' => '必須項目が不足しています'
                ), 400);
            }
            
            // シフト作成（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            $stmt = $pdo->prepare("
                INSERT INTO shifts (shift_date, start_time, end_time, slot_count, note) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([$shift_date, $start_time, $end_time, $slot_count, $note]);
            
            if ($result) {
                $shift_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
                $stmt->execute([$shift_id]);
                $shift = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $data = array(
                    'id' => $shift['id'],
                    'shift_date' => $shift['shift_date'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'note' => $shift['note'],
                    'slot_count' => $shift['slot_count'],
                    'available_slots' => $shift['slot_count'],
                    'assigned_users' => array(),
                    'created_at' => $shift['created_at'],
                    'updated_at' => $shift['updated_at']
                );
                
                return $this->response(array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'シフトが作成されました'
                ), 201);
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトの作成に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフトの作成に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * シフト削除
     */
    public function action_delete($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトIDが指定されていません'
                ), 400);
            }
            
            // シフト削除（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            
            // シフトの存在確認
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$shift) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトが見つかりません'
                ), 404);
            }
            
            // 参加者がいるかチェック
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM shift_assignments 
                WHERE shift_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$id]);
            $assigned_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($assigned_count > 0) {
                return $this->response(array(
                    'success' => false,
                    'message' => '参加者がいるシフトは削除できません。先に参加者を削除してください。'
                ), 400);
            }
            
            // シフト削除（CASCADEでshift_assignmentsも削除される）
            $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return $this->response(array(
                    'success' => true,
                    'message' => 'シフトを削除しました'
                ));
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトの削除に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフトの削除に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * シフト参加
     */
    public function action_join($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトIDが指定されていません'
                ), 400);
            }
            
            // 仮のユーザーID（実際の実装では認証から取得）
            $user_id = 1;
            
            // シフト参加（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            
            // シフトの存在確認
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$shift) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトが見つかりません'
                ), 404);
            }
            
            // 既に参加しているかチェック
            $stmt = $pdo->prepare("
                SELECT * FROM shift_assignments 
                WHERE shift_id = ? AND member_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$id, $user_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                return $this->response(array(
                    'success' => false,
                    'message' => '既にこのシフトに参加しています'
                ), 409);
            }
            
            // 空き枠チェック
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM shift_assignments 
                WHERE shift_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$id]);
            $assigned_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($assigned_count >= $shift['slot_count']) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトの定員に達しています'
                ), 409);
            }
            
            // 参加登録
            $stmt = $pdo->prepare("
                INSERT INTO shift_assignments (shift_id, member_id, status) 
                VALUES (?, ?, 'confirmed')
            ");
            $result = $stmt->execute([$id, $user_id]);
            
            if ($result) {
                return $this->response(array(
                    'success' => true,
                    'message' => 'シフトに参加しました'
                ));
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトの参加に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフトの参加に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * シフト参加取消
     */
    public function action_cancel($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトIDが指定されていません'
                ), 400);
            }
            
            // 仮のユーザーID（実際の実装では認証から取得）
            $user_id = 1;
            
            // シフト取消（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            
            // 参加登録の存在確認
            $stmt = $pdo->prepare("
                SELECT * FROM shift_assignments 
                WHERE shift_id = ? AND member_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$id, $user_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'このシフトに参加していません'
                ), 404);
            }
            
            // 参加取消（ステータスをcancelledに変更）
            $stmt = $pdo->prepare("
                UPDATE shift_assignments 
                SET status = 'cancelled' 
                WHERE shift_id = ? AND member_id = ?
            ");
            $result = $stmt->execute([$id, $user_id]);
            
            if ($result) {
                return $this->response(array(
                    'success' => true,
                    'message' => 'シフトの参加を取り消しました'
                ));
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトの取消に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフトの取消に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * シフト更新
     */
    public function action_update($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトIDが指定されていません'
                ), 400);
            }
            
            $shift_date = \Fuel\Core\Input::post('shift_date');
            $start_time = \Fuel\Core\Input::post('start_time');
            $end_time = \Fuel\Core\Input::post('end_time');
            $slot_count = \Fuel\Core\Input::post('slot_count', 1);
            $note = \Fuel\Core\Input::post('note', '');
            
            // バリデーション
            if (!$shift_date || !$start_time || !$end_time) {
                return $this->response(array(
                    'success' => false,
                    'message' => '必須項目が不足しています'
                ), 400);
            }
            
            if ($start_time >= $end_time) {
                return $this->response(array(
                    'success' => false,
                    'message' => '終了時間は開始時間より後にしてください'
                ), 400);
            }
            
            if ($slot_count < 1) {
                return $this->response(array(
                    'success' => false,
                    'message' => '定員数は1以上である必要があります'
                ), 400);
            }
            
            // シフト更新（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            
            // シフトの存在確認
            $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
            $existing_shift = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_shift) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトが見つかりません'
                ), 404);
            }
            
            // 現在の参加者数をチェック
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM shift_assignments 
                WHERE shift_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$id]);
            $current_assigned = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($slot_count < $current_assigned) {
                return $this->response(array(
                    'success' => false,
                    'message' => '定員数を現在の参加者数より少なくすることはできません'
                ), 400);
            }
            
            // シフト更新
            $stmt = $pdo->prepare("
                UPDATE shifts 
                SET shift_date = ?, start_time = ?, end_time = ?, slot_count = ?, note = ?
                WHERE id = ?
            ");
            $result = $stmt->execute([$shift_date, $start_time, $end_time, $slot_count, $note, $id]);
            
            if ($result) {
                // 更新されたシフトを取得
                $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
                $stmt->execute([$id]);
                $shift = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $data = array(
                    'id' => $shift['id'],
                    'shift_date' => $shift['shift_date'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'note' => $shift['note'],
                    'slot_count' => $shift['slot_count'],
                    'available_slots' => max(0, $shift['slot_count'] - $current_assigned),
                    'created_at' => $shift['created_at'],
                    'updated_at' => $shift['updated_at']
                );
                
                return $this->response(array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'シフトを更新しました'
                ));
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトの更新に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフトの更新に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * JSONレスポンスを返す
     */
    private function response($data, $status = 200)
    {
        $response = \Fuel\Core\Response::forge(json_encode($data), $status);
        $response->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }
}
