<?php

use Fuel\Core\Input;

class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    // GET /api/shifts
    public function get_index()
    {
        try {
            // 完全FuelPHP方式（最終版）
            // 設定を強制的に読み込み
            \Config::load('db', true, true);
            \Config::load('development/db', true, true);
            
            // データベース接続を強制的に初期化
            \DB::instance()->disconnect();
            \DB::instance()->connect();
            
            // 完全FuelPHP方式でシフトデータを取得
            $shifts = \DB::select()->from('shifts')
                ->order_by('shift_date', 'ASC')
                ->order_by('start_time', 'ASC')
                ->execute()
                ->as_array();
            
            return $this->response(['ok' => true, 'items' => $shifts]);
        } catch(Exception $e) {
            return $this->response(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/shifts/{id}
    public function get_show($id)
    {
        $shift = \DB::select()->from('shifts')->where('id', $id)->execute()->current();
        if (!$shift) return $this->response(['ok'=>false, 'error'=>'not_found'], 404);

        $assignments = \DB::query("
            SELECT sa.user_id, sa.status, sa.self_word, u.name, u.color
            FROM shift_assignments sa
            JOIN users u ON u.id = sa.user_id
            WHERE sa.shift_id = :id AND sa.status != 'cancelled'
            ORDER BY sa.created_at ASC
        ")->parameters(['id'=>$id])->execute()->as_array();

        $shift['assigned_users'] = $assignments;

        return $this->response(['ok'=>true, 'item'=>$shift]);
    }

    // POST /api/shifts
    public function post_index()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        list($id,) = \DB::insert('shifts')->set([
            'created_by'    => (int)($data['created_by'] ?? 1),
            'shift_date'    => $data['shift_date'] ?? null,
            'start_time'    => $data['start_time'] ?? null,
            'end_time'      => $data['end_time'] ?? null,
            'recruit_count' => (int)($data['recruit_count'] ?? 1),
            'free_text'     => $data['free_text'] ?? null,
            'created_at'    => \DB::expr('CURRENT_TIMESTAMP'),
        ])->execute();

        return $this->response(['ok'=>true, 'id'=>$id], 201);
    }

    // PUT /api/shifts/{id}
    public function put_update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        \DB::update('shifts')->set([
            'shift_date'    => $data['shift_date'] ?? \DB::expr('shift_date'),
            'start_time'    => $data['start_time'] ?? \DB::expr('start_time'),
            'end_time'      => $data['end_time'] ?? \DB::expr('end_time'),
            'recruit_count' => isset($data['recruit_count']) ? (int)$data['recruit_count'] : \DB::expr('recruit_count'),
            'free_text'     => array_key_exists('free_text',$data) ? $data['free_text'] : \DB::expr('free_text'),
            'updated_at'    => \DB::expr('CURRENT_TIMESTAMP'),
        ])->where('id', $id)->execute();

        return $this->response(['ok'=>true]);
    }

    // DELETE /api/shifts/{id}
    public function delete_delete($id)
    {
        \DB::delete('shift_assignments')->where('shift_id', $id)->execute();
        \DB::delete('shifts')->where('id', $id)->execute();
        return $this->response(['ok'=>true]);
    }
}