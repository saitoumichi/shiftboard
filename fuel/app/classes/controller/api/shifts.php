<?php
use Fuel\Core\Controller_Rest;
use Fuel\Core\Input;
use Fuel\Core\Validation;
use Fuel\Core\Response;

class Controller_Api_Shifts extends Controller_Rest
{
    protected $format = 'json';

    public function before()
    {
        parent::before();
        // レスポンスは常にJSON
        header('Content-Type: application/json; charset=UTF-8');
    }

    // POST /api/shifts
    public function post_index()
    {
        // JSON を安全に読む（x-www-form-urlencoded にもフォールバック）
        $raw  = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $in   = is_array($json) ? $json : Input::post();

        // created_by を必ずセット（認証があれば Auth::get('id') に置換）
        $created_by = 1; // TODO: 認証実装後は \Auth::get('id') などに

        // ---- Validation ----
        $val = Validation::forge();
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

        } catch (\Database_Exception $e) {
            // DBエラー（今回の created_by NULL など）はここで拾える
            return $this->response([
                'success' => false,
                'error'   => 'server_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}