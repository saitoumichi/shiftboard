<?php

class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    // POST /api/shifts
    public function post_index()
    {
        try {
            // JSONデータを取得
            $input = json_decode(\Fuel\Core\Input::body(), true);
            
            if (!$input) {
                return $this->response([
                    'success' => false,
                    'message' => '無効なJSONデータです',
                ], 400);
            }

            $shift = \Model_Shift::forge([
                'created_by'    => 1, // TODO: 認証導入後に置換
                'shift_date'    => $input['shift_date'],
                'start_time'    => $input['start_time'],
                'end_time'      => $input['end_time'],
                'recruit_count' => (int)$input['slot_count'],
                'free_text'     => $input['note'],
            ]);

            $shift->save();

            return $this->response([
                'success' => true,
                'message' => 'シフトが作成されました',
                'data'    => $shift->to_array(),
            ], 201);

        } catch (\Fuel\Core\Validation_Error $e) {
            return $this->response([
                'success' => false,
                'message' => 'バリデーションエラー: ' . $e->get_message(),
            ], 400);

        } catch (\Exception $e) {
            return $this->response([
                'success' => false,
                'message' => 'シフトの作成に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/shifts
    public function get_index()
    {
        try {
            $shifts = \Model_Shift::query()
                ->related('assignments')
                ->where('shift_date', '>=', date('Y-m-d'))
                ->order_by('shift_date', 'asc')
                ->order_by('start_time', 'asc')
                ->get();

            return $this->response([
                'success' => true,
                'data'    => $shifts,
            ]);

        } catch (\Exception $e) {
            return $this->response([
                'success' => false,
                'message' => 'シフトの取得に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/shifts/{id}
    public function get_view($id = null)
    {
        if (!$id) {
            return $this->response([
                'success' => false,
                'message' => 'シフトIDが必要です',
            ], 400);
        }

        try {
            $shift = \Model_Shift::find($id, [
                'related' => [
                    'assignments' => ['related' => ['user']]
                ]
            ]);

            if (!$shift) {
                return $this->response([
                    'success' => false,
                    'message' => 'シフトが見つかりません',
                ], 404);
            }

            return $this->response([
                'success' => true,
                'data'    => $shift,
            ]);

        } catch (\Exception $e) {
            return $this->response([
                'success' => false,
                'message' => 'シフトの取得に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }
}