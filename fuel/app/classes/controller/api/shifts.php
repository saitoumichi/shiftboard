<?php

use Fuel\Core\Input;

class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    // GET /api/shifts
    public function get_index()
    {
        $db_connect_error = null;
        $res = null;
    
        try {
            // 接続確認（軽いクエリでOK）
            try {
                $res = \DB::query('SELECT 1 AS ok')->execute()->current();
            } catch (\Database_Exception $e) {
                $db_connect_error = $e->getMessage();
                // 接続エラーなら素直に 500
                return $this->response(['ok' => false, 'error' => 'db_connect_failed', 'detail' => $db_connect_error], 500);
            }
    
            // シフト一覧
            $shifts = \DB::select()
                ->from('shifts')
                ->order_by('shift_date', 'ASC')
                ->order_by('start_time', 'ASC')
                ->execute()
                ->as_array();
    
            return $this->response(['ok' => true, 'items' => $shifts], 200);
    
        } catch (\Throwable $e) {
            return $this->response(
                ['ok' => false, 'error' => $e->getMessage()],
                500
            );
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
    
        // 必須チェック
        $required = ['shift_date','start_time','end_time'];
        foreach ($required as $k) {
            if (empty($data[$k])) {
                return $this->response(['ok'=>false,'error'=>"missing: {$k}"], 400);
            }
        }
        $recruit = (int)($data['recruit_count'] ?? 1);
        if ($recruit < 1) {
            return $this->response(['ok'=>false,'error'=>'recruit_count must be >= 1'], 400);
        }
    
        list($id,) = \DB::insert('shifts')->set([
            'created_by'    => (int)($data['created_by'] ?? 1),
            'shift_date'    => $data['shift_date'],
            'start_time'    => $data['start_time'],
            'end_time'      => $data['end_time'],
            'recruit_count' => $recruit,
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
        $exists = \DB::select('id')->from('shifts')->where('id',$id)->execute()->current();
        if (!$exists) {
            return $this->response(['ok'=>false,'error'=>'not_found'], 404);
        }
    
        \DB::start_transaction();
        try {
            \DB::delete('shift_assignments')->where('shift_id', $id)->execute();
            \DB::delete('shifts')->where('id', $id)->execute();
            \DB::commit_transaction();
            return $this->response(['ok'=>true], 200);
        } catch (\Throwable $e) {
            \DB::rollback_transaction();
            return $this->response(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }
}