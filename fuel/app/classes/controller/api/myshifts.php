<?php

/**
 * API My Shifts Controller
 * 
 * 自分のシフト管理用のAPIコントローラー
 */
class Controller_Api_Myshifts extends \Fuel\Core\Controller
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
            $start_date = \Fuel\Core\Input::get('start', date('Y-m-01'));
            $end_date = \Fuel\Core\Input::get('end', date('Y-m-t'));
            
            // 自分のシフト一覧を取得（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            $stmt = $pdo->prepare("
                SELECT s.*, COUNT(sa.id) as assigned_count
                FROM shifts s
                JOIN shift_assignments sa ON s.id = sa.shift_id
                WHERE sa.member_id = ? 
                AND sa.status != 'cancelled'
                AND s.shift_date >= ? 
                AND s.shift_date <= ?
                GROUP BY s.id
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$user_id, $start_date, $end_date]);
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
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
        $response = \Fuel\Core\Response::forge(json_encode($data), $status);
        $response->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }
}
