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
            
            // 自分のシフト一覧を取得
            $shifts = DB::select('s.*', DB::expr('COUNT(us.id) as assigned_count'))
                ->from('shifts', 's')
                ->join('user_shifts', 'us', 's.id = us.shift_id')
                ->where('us.user_id', $user_id)
                ->where('s.shift_date', '>=', $start_date)
                ->where('s.shift_date', '<=', $end_date)
                ->group_by('s.id')
                ->order_by('s.shift_date', 'ASC')
                ->order_by('s.start_time', 'ASC')
                ->execute()
                ->as_array();
            
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
