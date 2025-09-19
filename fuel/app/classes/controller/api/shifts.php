<?php

class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    public function before()
    {
        parent::before();
        // レスポンスは常にJSON
        header('Content-Type: application/json; charset=UTF-8');
    }

    public function get_index()
    {
        $from = \Fuel\Core\Input::get('from');
        $to   = \Fuel\Core\Input::get('to');
        $mine = \Fuel\Core\Input::get('mine');
        $user_id = \Fuel\Core\Input::get('user_id');

        $q = \Model_Shift::query()
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->related('assignments');

        if ($from) $q->where('shift_date', '>=', $from);
        if ($to)   $q->where('shift_date', '<=', $to);

        // 自分のシフトのみを取得する場合
        if ($mine && $user_id) {
            $q->where('assignments.user_id', $user_id);
        }

        $rows = $q->get();

        // フロント（shifts.js）が期待する形に整形
        $data = array_map(function($s) use ($mine, $user_id) {
            $assigned = isset($s->assignments) ? count($s->assignments) : 0;
            $slot     = (int)($s->recruit_count ?? 0);
            
            // 参加者情報を整形
            $assigned_users = [];
            if (isset($s->assignments) && is_array($s->assignments)) {
                foreach ($s->assignments as $assignment) {
                    $assigned_users[] = [
                        'id' => (int)$assignment->id,
                        'user_id' => (int)$assignment->user_id,
                        'name' => $assignment->user ? $assignment->user->name : 'Unknown User',
                        'status' => $assignment->status,
                        'self_word' => $assignment->self_word,
                        'color' => $assignment->user ? $assignment->user->color : '#000000'
                    ];
                }
            }
            
            // 自分のシフトの場合、自分のコメントをシフトレベルでも追加
            $self_word = null;
            if ($mine && $user_id && isset($s->assignments)) {
                foreach ($s->assignments as $assignment) {
                    if ($assignment->user_id == $user_id) {
                        $self_word = $assignment->self_word;
                        break;
                    }
                }
            }
            
            return [
                'id'              => (int)$s->id,
                'shift_date'      => (string)$s->shift_date,
                'start_time'      => substr((string)$s->start_time, 0, 5),
                'end_time'        => substr((string)$s->end_time, 0, 5),
                'slot_count'      => $slot,
                'assigned_users'  => $assigned_users,
                'assigned_count'  => $assigned,
                'available_slots' => max($slot - $assigned, 0),
                'note'            => (string)($s->free_text ?? ''),
                'self_word'       => $self_word, // 自分のコメントを追加
            ];
        }, $rows);

        return $this->response(['ok' => true, 'data' => array_values($data)]);
    }

        // 追加：GET /api/shifts/{id}
        public function get_show($id = null)
        {
            if (!$id) {
                return $this->response(['ok' => false, 'error' => 'invalid_id'], 400);
            }
            
        
            $shift = \Model_Shift::find($id, [
                'related' => ['assignments' => ['related' => ['user']]],
            ]);
        
            if (!$shift) {
                return $this->response(['ok' => false, 'error' => 'not_found'], 404);
            }
        
            // 参加者情報を整形
            $assigned_users = [];
            if (isset($shift->assignments) && is_array($shift->assignments)) {
                foreach ($shift->assignments as $assignment) {
                    $assigned_users[] = [
                        'id' => (int)$assignment->id,
                        'user_id' => (int)$assignment->user_id,
                        'name' => $assignment->user ? $assignment->user->name : 'Unknown User',
                        'status' => $assignment->status,
                        'self_word' => $assignment->self_word,
                        'color' => $assignment->user ? $assignment->user->color : '#000000'
                    ];
                }
            }
            
        $payload = $shift->to_array();
        $payload['assigned_users'] = $assigned_users;
        $payload['joined_count'] = count($assigned_users);
        $payload['remaining'] = max((int)$shift->recruit_count - count($assigned_users), 0);
        $payload['slot_count'] = (int)$shift->recruit_count;
        
        
            // ← ok & data に統一
        return $this->response([
            'ok' => true,
            'data'    => $payload
        ]);
    }
    

    // POST /api/shifts
    public function post_index()
    {
        // JSON を安全に読む（x-www-form-urlencoded にもフォールバック）
        $raw  = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $in   = is_array($json) ? $json : \Fuel\Core\Input::post();

        // created_by を必ずセット（セッションから取得）
        $created_by = \Fuel\Core\Session::get('user_id', 1);

        // ---- Validation ----
        $val = \Fuel\Core\Validation::forge();
        $val->add('shift_date', 'Shift Date')
            ->add_rule('required')
            ->add_rule('match_pattern', '/^\d{4}-\d{2}-\d{2}$/'); // YYYY-MM-DD

        $val->add('start_time', 'Start Time')
            ->add_rule('required')
            ->add_rule('match_pattern', '/^\d{2}:\d{2}$/'); // HH:MM

        $val->add('end_time', 'End Time')
            ->add_rule('required')
            ->add_rule('match_pattern', '/^\d{2}:\d{2}$/');

         $val->add('recruit_count', 'Recruit Count')
             ->add_rule('required')
             ->add_rule('valid_string', ['numeric'])
             // ここを修正：引数は $value のみでOK
             ->add_rule(function($value) {
                 if (!is_numeric($value)) return false;
                 return (int)$value >= 1; // 下限チェック
             }, 'must be >= 1');

        if ( ! $val->run($in)) {
            // 422 Unprocessable Entity で返す
            return $this->response([
                'ok'     => false,
                'errors' => $val->error()
            ], 422);
        }

        // 追加の業務ルール: 時刻の前後関係
        if (strtotime($in['end_time']) <= strtotime($in['start_time'])) {
            return $this->response([
                'ok'     => false,
                'errors' => ['end_time' => 'must be later than start_time']
            ], 422);
        }

        try {
            // 保存
            $shift = Model_Shift::forge([
                'created_by'   => $created_by,
                'shift_date'   => $in['shift_date'],
                'start_time'   => $in['start_time'],
                'end_time'     => $in['end_time'],
                'recruit_count'=> (int)$in['recruit_count'],
                'free_text'    => isset($in['free_text']) ? $in['free_text'] : null,
            ]);
            $shift->save();

            return $this->response([
                'ok'    => true,
                'shift' => $shift
            ], 201);

        } catch (\Exception $e) {
            // DBエラー（今回の created_by NULL など）はここで拾える
            return $this->response([
                'ok' => false,
                'error'   => 'server_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}