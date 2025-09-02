<?php

/**
 * API My Shifts Controller
 * 
 * 自分のシフト管理用のAPIコントローラー
 */
class Controller_Api_Myshifts extends Controller
{
    /**
     * 自分のシフト一覧取得
     */
    public function action_index()
    {
        try {
            // 仮のユーザーID（実際の実装では認証から取得）
            $user_id = 1;
            
            // 期間パラメータを取得
            $start_date = Input::get('start', date('Y-m-01'));
            $end_date = Input::get('end', date('Y-m-t'));
            
            // 自分のシフト一覧を取得（簡易版）
            $shifts = array(
                array(
                    'id' => 1,
                    'shift_date' => '2025-09-03',
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'note' => '平日シフト',
                    'slot_count' => 2,
                    'assigned_count' => 2,
                    'created_at' => '2025-09-02 20:45:48',
                    'updated_at' => null
                ),
                array(
                    'id' => 3,
                    'shift_date' => '2025-09-05',
                    'start_time' => '10:00:00',
                    'end_time' => '18:00:00',
                    'note' => '遅番シフト',
                    'slot_count' => 1,
                    'assigned_count' => 1,
                    'created_at' => '2025-09-02 20:45:48',
                    'updated_at' => null
                )
            );
            
            // データを整形
            $data = array();
            foreach ($shifts as $shift) {
                $data[] = array(
                    'id' => $shift['id'],
                    'shift_date' => $shift['shift_date'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'note' => $shift['note'],
                    'slot_count' => $shift['slot_count'],
                    'assigned_count' => $shift['assigned_count'],
                    'created_at' => $shift['created_at'],
                    'updated_at' => $shift['updated_at']
                );
            }
            
            return $this->response(array(
                'success' => true,
                'data' => $data,
                'message' => '自分のシフト一覧を取得しました'
            ));
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => '自分のシフト一覧の取得に失敗しました: ' . $e->getMessage()
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
