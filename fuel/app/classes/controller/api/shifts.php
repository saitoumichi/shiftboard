<?php

/**
 * API Shifts Controller
 * 
 * シフト管理用のAPIコントローラー
 */
class Controller_Api_Shifts extends Controller_Base
{
    /**
     * シフト一覧取得
     */
    public function action_index()
    {
        try {
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
     * JSONレスポンスを返す
     */
    private function response($data, $status = 200)
    {
        $response = Response::forge(json_encode($data), $status);
        $response->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }
}