<?php
class Controller_Api_Shift_Assignments extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    // GET /api/shift_assignments?shift_id=...
    public function get_index()
    {
        $shift_id = \Input::get('shift_id');
        if (! $shift_id) {
            return $this->response(['ok' => false, 'error' => 'shift_id が必要です'], 400);
        }

        $assignments = \Model_Shift_Assignment::query()
            ->where('shift_id', $shift_id)
            ->related('user')   // 必要なら関連も取得
            ->get();

        return $this->response(['ok' => true, 'data' => $assignments]);
    }
}