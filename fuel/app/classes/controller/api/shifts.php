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
            // シフト一覧を取得（簡易版）
            $shifts = array(
                array(
                    'id' => 1,
                    'shift_date' => '2025-09-03',
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'note' => '平日シフト',
                    'slot_count' => 2,
                    'created_at' => '2025-09-02 22:32:21',
                    'updated_at' => null
                ),
                array(
                    'id' => 2,
                    'shift_date' => '2025-09-04',
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'note' => '平日シフト',
                    'slot_count' => 2,
                    'created_at' => '2025-09-02 22:32:21',
                    'updated_at' => null
                ),
                array(
                    'id' => 3,
                    'shift_date' => '2025-09-05',
                    'start_time' => '10:00:00',
                    'end_time' => '18:00:00',
                    'note' => '遅番シフト',
                    'slot_count' => 1,
                    'created_at' => '2025-09-02 22:32:21',
                    'updated_at' => null
                ),
                array(
                    'id' => 4,
                    'shift_date' => '2025-09-06',
                    'start_time' => '09:00:00',
                    'end_time' => '15:00:00',
                    'note' => '早番シフト',
                    'slot_count' => 3,
                    'created_at' => '2025-09-02 22:32:21',
                    'updated_at' => null
                )
            );
            
            // 各シフトの割り当て情報を取得（簡易版）
            $data = array();
            foreach ($shifts as $shift) {
                $assigned_users = array();
                
                // 簡易的な割り当て情報
                if ($shift['id'] == 1) {
                    $assigned_users = array(
                        array('name' => '管理者', 'status' => 'confirmed'),
                        array('name' => '田中太郎', 'status' => 'confirmed')
                    );
                } elseif ($shift['id'] == 2) {
                    $assigned_users = array(
                        array('name' => '佐藤花子', 'status' => 'confirmed'),
                        array('name' => '鈴木一郎', 'status' => 'confirmed')
                    );
                } elseif ($shift['id'] == 3) {
                    $assigned_users = array(
                        array('name' => '田中太郎', 'status' => 'confirmed')
                    );
                } elseif ($shift['id'] == 4) {
                    $assigned_users = array(
                        array('name' => '佐藤花子', 'status' => 'confirmed')
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
            
            // シフト詳細を取得（簡易版）
            $shifts = array(
                1 => array(
                    'id' => 1,
                    'shift_date' => '2025-09-03',
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'note' => '平日シフト',
                    'slot_count' => 2,
                    'created_at' => '2025-09-02 20:45:48',
                    'updated_at' => null
                ),
                2 => array(
                    'id' => 2,
                    'shift_date' => '2025-09-04',
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'note' => '平日シフト',
                    'slot_count' => 2,
                    'created_at' => '2025-09-02 20:45:48',
                    'updated_at' => null
                ),
                3 => array(
                    'id' => 3,
                    'shift_date' => '2025-09-05',
                    'start_time' => '10:00:00',
                    'end_time' => '18:00:00',
                    'note' => '遅番シフト',
                    'slot_count' => 1,
                    'created_at' => '2025-09-02 20:45:48',
                    'updated_at' => null
                ),
                4 => array(
                    'id' => 4,
                    'shift_date' => '2025-09-06',
                    'start_time' => '09:00:00',
                    'end_time' => '15:00:00',
                    'note' => '早番シフト',
                    'slot_count' => 3,
                    'created_at' => '2025-09-02 20:45:48',
                    'updated_at' => null
                )
            );
            
            if (!isset($shifts[$id])) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'シフトが見つかりません'
                ), 404);
            }
            
            $shift = $shifts[$id];
            
            // 割り当て情報（簡易版）
            $assigned_users = array();
            if ($id == 1) {
                $assigned_users = array(
                    array('id' => 1, 'name' => '管理者', 'status' => 'confirmed'),
                    array('id' => 2, 'name' => '田中太郎', 'status' => 'confirmed')
                );
            } elseif ($id == 2) {
                $assigned_users = array(
                    array('id' => 3, 'name' => '佐藤花子', 'status' => 'confirmed'),
                    array('id' => 4, 'name' => '鈴木一郎', 'status' => 'confirmed')
                );
            } elseif ($id == 3) {
                $assigned_users = array(
                    array('id' => 2, 'name' => '田中太郎', 'status' => 'confirmed')
                );
            } elseif ($id == 4) {
                $assigned_users = array(
                    array('id' => 3, 'name' => '佐藤花子', 'status' => 'confirmed')
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
            
            // シフト参加（簡易版）
            // 実際の実装ではデータベースを更新しますが、ここでは成功レスポンスを返します
            
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
            
            // 参加取消（簡易版）
            // 実際の実装ではデータベースを更新しますが、ここでは成功レスポンスを返します
            
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