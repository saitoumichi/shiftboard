<?php

class Controller_Api_Shift_Assignments extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    // GET /api/shift_assignments?shift_id=3
    public function get_index()
    {
        $sid = (int) \Fuel\Core\Input::get('shift_id');
        if (!$sid) return $this->response(['ok'=>false, 'error'=>'shift_id required'], 400);

        $items = \DB::query("
            SELECT sa.id, sa.shift_id, sa.user_id, sa.status, sa.self_word,
                   sa.created_at, u.name, u.color
            FROM shift_assignments sa
            JOIN users u ON u.id = sa.user_id
            WHERE sa.shift_id = :sid
        ")->parameters(['sid'=>$sid])->execute()->as_array();

        return $this->response(['ok'=>true, 'items'=>$items]);
    }

    // POST /api/shifts/{id}/join   （ログイン未導入のためuser_idを仮で受ける）
    public function post_join($id)
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user_id = (int)($data['user_id'] ?? 1);

        // 既存チェック（キャンセル含む）
        $existing = \DB::select()->from('shift_assignments')
            ->where('shift_id', $id)->where('user_id', $user_id)->execute()->current();

        if ($existing) {
            \DB::update('shift_assignments')->set([
                'status' => 'confirmed', 'updated_at' => \DB::expr('CURRENT_TIMESTAMP')
            ])->where('id', $existing['id'])->execute();
        } else {
            \DB::insert('shift_assignments')->set([
                'shift_id'   => $id,
                'user_id'    => $user_id,
                'status'     => 'confirmed',
                'created_at' => \DB::expr('CURRENT_TIMESTAMP'),
            ])->execute();
        }

        return $this->response(['ok'=>true]);
    }

    // POST /api/shifts/{id}/cancel
    public function post_cancel($id)
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user_id = (int)($data['user_id'] ?? 1);

        \DB::update('shift_assignments')->set([
            'status' => 'cancelled', 'updated_at' => \DB::expr('CURRENT_TIMESTAMP')
        ])->where('shift_id', $id)->where('user_id', $user_id)->where('status', '!=', 'cancelled')->execute();

        return $this->response(['ok'=>true]);
    }
}