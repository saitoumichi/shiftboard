<?php

use Fuel\Core\DB;
use Fuel\Core\Session;

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

        // DB::selectを使用してJOINクエリで最適化
        $query = DB::select(
            's.id',
            's.shift_date',
            's.start_time',
            's.end_time',
            's.recruit_count',
            's.free_text',
            's.created_by',
            's.created_at',
            's.updated_at',
            DB::expr('COUNT(sa.id) as assigned_count'),
            DB::expr('GROUP_CONCAT(
                CONCAT(
                    sa.id, ":", 
                    sa.user_id, ":", 
                    sa.status, ":", 
                    COALESCE(sa.self_word, ""), ":", 
                    COALESCE(u.name, "Unknown User"), ":", 
                    COALESCE(u.color, "#000000")
                ) 
                SEPARATOR "|"
            ) as assignments_data')
        )
        ->from(['shifts', 's'])
        ->join(['shift_assignments', 'sa'], 'LEFT')
            ->on('s.id', '=', 'sa.shift_id')
            ->on('sa.status', '!=', DB::expr("'cancelled'"))
        ->join(['users', 'u'], 'LEFT')
            ->on('sa.user_id', '=', 'u.id')
        ->group_by('s.id', 's.shift_date', 's.start_time', 's.end_time', 's.recruit_count', 's.free_text', 's.created_by', 's.created_at', 's.updated_at')
        ->order_by('s.shift_date', 'asc')
        ->order_by('s.start_time', 'asc');

        if ($from) $query->where('s.shift_date', '>=', $from);
        if ($to)   $query->where('s.shift_date', '<=', $to);

        // 自分のシフトのみを取得する場合
        if ($mine && $user_id) {
            $query->where('sa.user_id', $user_id);
        }

        $rows = $query->execute()->as_array();

        // フロント（shifts.js）が期待する形に整形
        $data = array_map(function($s) use ($mine, $user_id) {
            $assigned = (int)($s['assigned_count'] ?? 0);
            $slot     = (int)($s['recruit_count'] ?? 0);
            
            // 参加者情報を整形（GROUP_CONCATから解析）
            $assigned_users = [];
            $self_word = null;
            
            if (!empty($s['assignments_data'])) {
                $assignments = explode('|', $s['assignments_data']);
                foreach ($assignments as $assignment_str) {
                    if (empty($assignment_str)) continue;
                    
                    $parts = explode(':', $assignment_str);
                    if (count($parts) >= 6) {
                        $assignment = [
                            'id' => (int)$parts[0],
                            'user_id' => (int)$parts[1],
                            'status' => $parts[2],
                            'self_word' => $parts[3],
                            'name' => $parts[4],
                            'color' => $parts[5]
                        ];
                        $assigned_users[] = $assignment;
                        
                        // 自分のシフトの場合、自分のコメントをシフトレベルでも追加
                        if ($mine && $user_id && $assignment['user_id'] == $user_id) {
                            $self_word = $assignment['self_word'];
                        }
                    }
                }
            }
            
            return [
                'id'              => (int)$s['id'],
                'shift_date'      => (string)$s['shift_date'],
                'start_time'      => substr((string)$s['start_time'], 0, 5),
                'end_time'        => substr((string)$s['end_time'], 0, 5),
                'slot_count'      => $slot,
                'assigned_users'  => $assigned_users,
                'assigned_count'  => $assigned,
                'available_slots' => max($slot - $assigned, 0),
                'note'            => (string)($s['free_text'] ?? ''),
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
        // Input::json()を使用してJSONを安全に受信
        $in = \Fuel\Core\Input::json() ?: \Fuel\Core\Input::post();

        // created_by を必ずセット（セッションから取得）
        $created_by = Session::get('user_id', 1);

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

    // PUT /api/shifts/{id} - シフト更新
    public function put_show($id = null)
    {
        if (!$id) {
            return $this->response(['ok' => false, 'error' => 'invalid_id'], 400);
        }

        // 現在のユーザーIDを取得
        $current_user_id = Session::get('user_id');
        if (!$current_user_id) {
            return $this->response(['ok' => false, 'error' => 'not_authenticated'], 401);
        }

        // シフトを取得
        $shift = \Model_Shift::find($id);
        if (!$shift) {
            return $this->response(['ok' => false, 'error' => 'not_found'], 404);
        }

        // 作成者のみ編集可能
        if ($shift->created_by !== (int)$current_user_id) {
            return $this->response(['ok' => false, 'error' => 'forbidden'], 403);
        }

        // Input::json()を使用してJSONを安全に受信
        $in = \Fuel\Core\Input::json() ?: [];

        // バリデーション
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
            ->add_rule(function($value) {
                if (!is_numeric($value)) return false;
                return (int)$value >= 1; // 下限チェック
            }, 'must be >= 1');

        if (!$val->run($in)) {
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
            // シフト情報を更新
            $shift->shift_date = $in['shift_date'];
            $shift->start_time = $in['start_time'];
            $shift->end_time = $in['end_time'];
            $shift->recruit_count = (int)$in['recruit_count'];
            $shift->free_text = isset($in['free_text']) ? $in['free_text'] : null;
            $shift->updated_at = date('Y-m-d H:i:s');
            $shift->save();

            return $this->response([
                'ok' => true,
                'message' => 'シフト情報を更新しました',
                'shift' => $shift->to_array()
            ]);

        } catch (\Exception $e) {
            return $this->response([
                'ok' => false,
                'error' => 'server_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}