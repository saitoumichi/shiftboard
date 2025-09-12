<?php
class Controller_Api_Shift_Assignments extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    public function get_index($shift_id = null)
    {
        \DB::query("USE `shiftboard`")->execute();

        // /api/shift_assignments?shift_id=3 でも使えるよう保険
        if ($shift_id === null) {
            $q = \Input::get('shift_id');
            if ($q !== null) $shift_id = (int)$q;
        }

        if ($shift_id === null) {
            return $this->response(['ok'=>false,'error'=>'shift_id is required'], 400);
        }

        $rows = \DB::query("
          SELECT sa.id, sa.shift_id, sa.user_id, u.name,
                 sa.self_word, sa.status, sa.created_at, sa.updated_at
          FROM shift_assignments sa
          JOIN users u ON u.id = sa.user_id
          WHERE sa.shift_id = :sid AND sa.status <> 'cancelled'
          ORDER BY sa.id
        ")->parameters(['sid' => (int)$shift_id])->execute()->as_array();

        return $this->response(['ok'=>true,'items'=>$rows], 200);
    }
}