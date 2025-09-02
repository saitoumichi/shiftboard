<?php

/**
 * API Shifts Controller
 * 
 * シフト管理用のAPIコントローラー
 */
class Controller_Api_Shifts extends Controller
{
    /**
     * シフト一覧取得
     */
    public function action_index()
    {
        try {
            // デバッグ: シフト数を確認
            $count = DB::select(DB::expr('COUNT(*) as count'))->from('shifts')->execute()->get('count');
            error_log("API Shifts count: " . $count);
            
            // シフト一覧を取得
            $shifts = DB::select()->from('shifts')->order_by('shift_date', 'ASC')->execute()->as_array();
            
            // 各シフトの割り当て情報を取得
            $data = array();
            foreach ($shifts as $shift) {
                $assignments = DB::select('u.name', 'us.role')
                    ->from('user_shifts', 'us')
                    ->join('users', 'u', 'us.user_id = u.id')
                    ->where('us.shift_id', $shift['id'])
                    ->execute()
                    ->as_array();
                
                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'name' => $assignment['name'],
                        'role' => $assignment['role']
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
     * シフト作成
     */
    public function action_create()
    {
        try {
            $shift_date = Input::post('shift_date');
            $start_time = Input::post('start_time');
            $end_time = Input::post('end_time');
            $slot_count = Input::post('slot_count', 1);
            $note = Input::post('note', '');
            
            if (!$shift_date || !$start_time || !$end_time) {
                return $this->response(array(
                    'success' => false,
                    'message' => '必須項目が不足しています'
                ), 400);
            }
            
            $result = DB::query(
                "INSERT INTO shifts (shift_date, start_time, end_time, slot_count, note) 
                 VALUES ('$shift_date', '$start_time', '$end_time', $slot_count, '$note')")
                ->execute();
            
            if ($result) {
                $shift_id = DB::query('SELECT LAST_INSERT_ID() as id')->execute()->get('id');
                $shift = DB::query("SELECT * FROM shifts WHERE id = $shift_id")
                    ->execute()
                    ->as_array()[0];
                
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
     * シフト詳細取得
     */
    public function action_show($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトIDが指定されていません'
                ), 400);
            }
            
            // シフト詳細を取得
            $shift = DB::select()->from('shifts')->where('id', $id)->execute()->current();
            
            if (!$shift) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトが見つかりません'
                ), 404);
            }
            
            // 割り当て情報を取得
            $assignments = DB::select('u.id', 'u.name', 'us.role')
                ->from('user_shifts', 'us')
                ->join('users', 'u', 'us.user_id = u.id')
                ->where('us.shift_id', $id)
                ->execute()
                ->as_array();
            
            $assigned_users = array();
            foreach ($assignments as $assignment) {
                $assigned_users[] = array(
                    'id' => $assignment['id'],
                    'name' => $assignment['name'],
                    'role' => $assignment['role']
                );
            }
            
            $data = array(
                'id' => $shift['id'],
                'title' => 'シフト ' . $shift['shift_date'],
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
            
            $result = DB::query("DELETE FROM shifts WHERE id = $id")->execute();
            
            if ($result) {
                return $this->response(array(
                    'success' => true,
                    'message' => 'シフトが削除されました'
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
            
            // シフトの存在確認
            $shift = DB::select()->from('shifts')->where('id', $id)->execute()->current();
            if (!$shift) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトが見つかりません'
                ), 404);
            }
            
            // 既に参加しているかチェック
            $existing = DB::select()->from('user_shifts')
                ->where('shift_id', $id)
                ->where('user_id', $user_id)
                ->execute()
                ->current();
            
            if ($existing) {
                return $this->response(array(
                    'success' => false,
                    'message' => '既にこのシフトに参加しています'
                ), 409);
            }
            
            // 空き枠チェック
            $assigned_count = DB::select(DB::expr('COUNT(*) as count'))
                ->from('user_shifts')
                ->where('shift_id', $id)
                ->execute()
                ->get('count');
            
            if ($assigned_count >= $shift['slot_count']) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトの定員に達しています'
                ), 409);
            }
            
            // 参加登録
            DB::query("INSERT INTO user_shifts (user_id, shift_id, role) VALUES ($user_id, $id, 'member')")->execute();
            
            return $this->response(array(
                'success' => true,
                'message' => 'シフトに参加しました'
            ));
            
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
            
            // 参加登録の存在確認
            $existing = DB::select()->from('user_shifts')
                ->where('shift_id', $id)
                ->where('user_id', $user_id)
                ->execute()
                ->current();
            
            if (!$existing) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'このシフトに参加していません'
                ), 404);
            }
            
            // 参加取消
            DB::query("DELETE FROM user_shifts WHERE shift_id = $id AND user_id = $user_id")->execute();
            
            return $this->response(array(
                'success' => true,
                'message' => 'シフトの参加を取り消しました'
            ));
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'シフトの取消に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * JSONレスポンスを返す
     */
    private function response($data, $status = 200)
    {
        $response = Response::forge(json_encode($data), $status);
        $response->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }
}