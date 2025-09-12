<?php

class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    public function before()
    {
        parent::before();
        \DB::query("USE `shiftboard`")->execute(); // スキーマ固定（または各FROMにスキーマ修飾）
    }

    /** GET /api/shifts */
    public function get_index()
    {
        try {
            $page = (int)\Input::get('page', 1);
            $per  = (int)\Input::get('per_page', 20);
            $only_open = (int)\Input::get('only_open', 0) === 1;

            $res = Model_Shifts::list_with_counts([
                'page' => $page, 'per' => $per, 'only_open' => $only_open,
            ]);
            return $this->response(['ok'=>true] + $res, 200);
        } catch (\Throwable $e) {
            return $this->response(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /** GET /api/shifts/{id} */
    public function get_show($id)
    {
        try {
            $row = Model_Shifts::find_one((int)$id);
            if (!$row) return $this->response(['ok'=>false,'error'=>'Not found'], 404);
            return $this->response(['ok'=>true,'item'=>$row], 200);
        } catch (\Throwable $e) {
            return $this->response(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /** POST /api/shifts */
    public function post_index()
    {
        try {
            $p = json_decode(\Input::body(), true) ?: [];
            $id = Model_Shifts::create($p);
            $row = Model_Shifts::find_one($id);
            return $this->response(['ok'=>true,'item'=>$row], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->response(['ok'=>false,'error'=>$e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->response(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /** PUT /api/shifts/{id} */
    public function put_update($id)
    {
        try {
            $p = json_decode(\Input::body(), true) ?: [];
            $ok = Model_Shifts::update((int)$id, $p);
            if (!$ok) return $this->response(['ok'=>false,'error'=>'No changes'], 400);
            $row = Model_Shifts::find_one((int)$id);
            return $this->response(['ok'=>true,'item'=>$row], 200);
        } catch (\Throwable $e) {
            return $this->response(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /** DELETE /api/shifts/{id} */
    public function delete_delete($id)
    {
        try {
            $ok = Model_Shifts::delete((int)$id);
            if (!$ok) return $this->response(['ok'=>false,'error'=>'Not found'], 404);
            return $this->response(['ok'=>true], 200);
        } catch (\Throwable $e) {
            return $this->response(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /** GET /api/shifts/{id}/assignments（任意：参加者一覧） */
    public function get_assignments($id)
    {
        $rows = \DB::query("
          SELECT sa.id, sa.shift_id, sa.user_id, u.name,
                 sa.self_word, sa.status, sa.created_at, sa.updated_at
          FROM shift_assignments sa
          JOIN users u ON u.id = sa.user_id
          WHERE sa.shift_id = :sid AND sa.status <> 'cancelled'
          ORDER BY sa.id
        ")->parameters(['sid' => (int)$id])->execute()->as_array();

        // 型整形（見やすく）
        $items = array_map(function($r){
          return [
            'id'         => (int)$r['id'],
            'shift_id'   => (int)$r['shift_id'],
            'user_id'    => (int)$r['user_id'],
            'name'       => (string)$r['name'],
            'self_word'  => $r['self_word'] ?? null,
            'status'     => (string)$r['status'],
            'created_at' => (string)$r['created_at'],
            'updated_at' => $r['updated_at'],
          ];
        }, $rows);

        return $this->response(['ok'=>true,'items'=>$items], 200);
    }
}