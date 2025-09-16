<?php
class Controller_Api_Shift_Assignments extends \Fuel\Core\Controller_Rest

{
    protected $format = 'json';

    public function get_index()
    {
        $shift_id = \Fuel\Core\Input::get('shift_id');
        if (! $shift_id) {
            return $this->response(['ok' => false, 'error' => 'shift_id が必要です'], 400);
        }

        $assignments = \Model_Shift_Assignment::query()
            ->where('shift_id', $shift_id)
            ->related('user')   // 必要なら関連も取得
            ->get();

        return $this->response(['ok' => true, 'data' => $assignments]);
    }

    /**
     * 新しいshift_assignmentsレコードを作成するAPIエンドポイント
     * POST /api/shift_assignments
     */
    public function post_index()
    {
        $input = $this->get_input_data();

        // バリデーション
        if (!$this->validate_input($input)) {
            return $this->response([
                'success' => false,
                'error' => 'validation_failed',
                'errors' => $this->get_validation_errors()
            ], 400);
        }

        try {
            // シフトとユーザーの存在確認（ORMの関連を使用）
            $shift = \Model_Shift::find($input['shift_id']);
            $user = \Model_User::find($input['user_id']);

            if (!$shift || !$user) {
                return $this->response([
                    'success' => false,
                    'error' => $shift ? 'user_not_found' : 'shift_not_found',
                    'message' => $shift ? '指定されたユーザーが見つかりません' : '指定されたシフトが見つかりません'
                ], 404);
            }

            // 重複参加チェック
            if ($this->is_duplicate_assignment($input['shift_id'], $input['user_id'])) {
                return $this->response([
                    'success' => false,
                    'error' => 'already_joined',
                    'message' => '既にこのシフトに参加しています'
                ], 409);
            }

            // 定員チェック
            if ($this->is_shift_full($shift)) {
                return $this->response([
                    'success' => false,
                    'error' => 'shift_full',
                    'message' => 'このシフトの定員に達しています',
                    'details' => $this->get_capacity_details($shift)
                ], 409);
            }

            // 新しい割り当てを作成（ORMのforgeとsaveを使用）
            $assignment = \Model_Shift_Assignment::forge([
                'shift_id' => $input['shift_id'],
                'user_id' => $input['user_id'],
                'status' => 'assigned',
                'self_word' => $input['self_word'] ?? null,
            ])->save();

            // 関連データを含めて再取得
            $assignment = \Model_Shift_Assignment::find($assignment->id, [
                'related' => ['user', 'shift']
            ]);

            return $this->response([
                'success' => true,
                'message' => 'シフトへの参加が完了しました',
                'data' => $this->format_assignment_data($assignment)
            ], 201);

        } catch (\Exception $e) {
            return $this->response([
                'success' => false,
                'error' => 'server_error',
                'message' => 'サーバーエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 新しいshift_assignmentsレコードを作成する専用エンドポイント
     * POST /api/shift_assignments/create
     */
    public function post_create()
    {
        $input = $this->get_input_data();

        // 拡張バリデーション（ステータスを含む）
        if (!$this->validate_create_input($input)) {
            return $this->response([
                'success' => false,
                'error' => 'validation_failed',
                'message' => '入力内容に誤りがあります',
                'errors' => $this->get_validation_errors()
            ], 400);
        }

        try {
            // シフトとユーザーの存在確認
            $shift = \Model_Shift::find($input['shift_id']);
            $user = \Model_User::find($input['user_id']);

            if (!$shift || !$user) {
                return $this->response([
                    'success' => false,
                    'error' => $shift ? 'user_not_found' : 'shift_not_found',
                    'message' => $shift ? '指定されたユーザーが見つかりません' : '指定されたシフトが見つかりません'
                ], 404);
            }

            // 重複参加チェック（キャンセル済みは除く）
            if ($this->is_duplicate_assignment_active($input['shift_id'], $input['user_id'])) {
                return $this->response([
                    'success' => false,
                    'error' => 'already_joined',
                    'message' => '既にこのシフトに参加しています'
                ], 409);
            }

            // 定員チェック（キャンセル済みは除く）
            if ($input['status'] !== 'cancelled' && $this->is_shift_full($shift)) {
                return $this->response([
                    'success' => false,
                    'error' => 'shift_full',
                    'message' => 'このシフトの定員に達しています',
                    'details' => $this->get_capacity_details($shift)
                ], 409);
            }

            // 新しい割り当てを作成
            $assignment = \Model_Shift_Assignment::forge([
                'shift_id' => $input['shift_id'],
                'user_id' => $input['user_id'],
                'status' => $input['status'] ?? 'assigned',
                'self_word' => $input['self_word'] ?? null,
            ])->save();

            // 関連データを含めて再取得
            $assignment = \Model_Shift_Assignment::find($assignment->id, [
                'related' => ['user', 'shift']
            ]);

            return $this->response([
                'success' => true,
                'message' => 'シフト割り当てが正常に作成されました',
                'data' => $this->format_assignment_data_with_shift($assignment)
            ], 201);

        } catch (\Exception $e) {
            return $this->response([
                'success' => false,
                'error' => 'server_error',
                'message' => 'サーバーエラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 入力データを取得
     */
    private function get_input_data()
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        return is_array($json) ? $json : \Fuel\Core\Input::post();
    }

    /**
     * 入力データのバリデーション
     */
    private function validate_input($input)
    {
        $validation = \Fuel\Core\Validation::forge();
        $validation->add('shift_id', 'シフトID')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('numeric_min', 1);
        $validation->add('user_id', 'ユーザーID')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('numeric_min', 1);
        $validation->add('self_word', 'コメント')
            ->add_rule('max_length', 500);

        return $validation->run($input);
    }

    /**
     * バリデーションエラーを取得
     */
    private function get_validation_errors()
    {
        $errors = array();
        foreach (\Fuel\Core\Validation::instance()->error() as $field => $error) {
            $errors[$field] = $error->get_message();
        }
        return $errors;
    }

    /**
     * 重複参加チェック
     */
    private function is_duplicate_assignment($shift_id, $user_id)
    {
        return \Model_Shift_Assignment::exists_for_shift_and_user($shift_id, $user_id, false);
    }

    /**
     * 定員チェック
     */
    private function is_shift_full($shift)
    {
        return $shift->joined_count() >= $shift->recruit_count;
    }

    /**
     * 定員詳細情報を取得
     */
    private function get_capacity_details($shift)
    {
        $current_count = $shift->joined_count();
        $recruit_count = (int)$shift->recruit_count;
        return [
            'current_count' => $current_count,
            'recruit_count' => $recruit_count,
            'remaining' => max(0, $recruit_count - $current_count)
        ];
    }

    /**
     * 拡張バリデーション（ステータスを含む）
     */
    private function validate_create_input($input)
    {
        $validation = \Fuel\Core\Validation::forge();
        $validation->add('shift_id', 'シフトID')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('numeric_min', 1);
        $validation->add('user_id', 'ユーザーID')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('numeric_min', 1);
        $validation->add('status', 'ステータス')
            ->add_rule('in_array', ['assigned', 'confirmed', 'cancelled']);
        $validation->add('self_word', 'コメント')
            ->add_rule('max_length', 500);

        return $validation->run($input);
    }

    /**
     * アクティブな重複参加チェック（キャンセル済みは除く）
     */
    private function is_duplicate_assignment_active($shift_id, $user_id)
    {
        return \Model_Shift_Assignment::exists_for_shift_and_user($shift_id, $user_id, true);
    }

    /**
     * 割り当てデータをフォーマット
     */
    private function format_assignment_data($assignment)
    {
        return [
            'id' => (int)$assignment->id,
            'shift_id' => (int)$assignment->shift_id,
            'user_id' => (int)$assignment->user_id,
            'user_name' => $assignment->user ? $assignment->user->name : 'Unknown User',
            'user_color' => $assignment->user ? $assignment->user->color : '#000000',
            'status' => $assignment->status,
            'self_word' => $assignment->self_word,
            'created_at' => $assignment->created_at,
            'updated_at' => $assignment->updated_at
        ];
    }

    /**
     * シフト情報を含む割り当てデータをフォーマット
     */
    private function format_assignment_data_with_shift($assignment)
    {
        $data = $this->format_assignment_data($assignment);
        $data['shift_info'] = [
            'shift_date' => $assignment->shift ? $assignment->shift->shift_date : null,
            'start_time' => $assignment->shift ? $assignment->shift->start_time : null,
            'end_time' => $assignment->shift ? $assignment->shift->end_time : null,
            'recruit_count' => $assignment->shift ? (int)$assignment->shift->recruit_count : null
        ];
        return $data;
    }

    public function before()
    {
        parent::before();
        header('Content-Type: application/json; charset=UTF-8');
    }

    // /api/shifts/{id}/join へ POST
public function post_join($shift_id)
{
    $raw  = file_get_contents('php://input');
    $json = json_decode($raw, true);
    $in   = is_array($json) ? $json : \Fuel\Core\Input::post();

    $user_id = (int)($in['user_id'] ?? 1); // 認証未実装なら仮
    $self_word = isset($in['self_word']) ? trim($in['self_word']) : null; // コメントを取得
    
    try {
        // 重複参加チェック
        $existing = \Model_Shift_Assignment::query()
            ->where('shift_id', (int)$shift_id)
            ->where('user_id', $user_id)
            ->get_one();
        
        if ($existing) {
            return $this->response(['ok' => false, 'error' => 'already_joined'], 409);
        }
        
        // ここで Model_Shift_Assignment を作成
        $assign = \Model_Shift_Assignment::forge([
            'shift_id' => (int)$shift_id,
            'user_id'  => $user_id,
            'status'   => 'assigned', //デフォルト値を追加
            'self_word' => $self_word, // コメントを保存
        ]);
        $assign->save();

        return $this->response(['ok'=>true, 'assignment'=>$assign], 201);
    } catch (\Exception $e) {
        return $this->response(['ok'=>false, 'error'=>'server_error', 'message'=>$e->getMessage()], 500);
    }
}

// /api/shifts/{id}/cancel へ POST
public function post_cancel($shift_id)
{
    $raw  = file_get_contents('php://input');
    $json = json_decode($raw, true);
    $in   = is_array($json) ? $json : \Fuel\Core\Input::post();

    $user_id = (int)($in['user_id'] ?? 1);
    try {
        $assign = \Model_Shift_Assignment::query()
            ->where('shift_id', (int)$shift_id)
            ->where('user_id',  $user_id)
            ->get_one();

        if ( ! $assign) {
            return $this->response(['ok'=>false, 'error'=>'not_found'], 404);
        }
        $assign->delete();
        return $this->response(['ok'=>true], 200);
    } catch (\Exception $e) {
        return $this->response(['ok'=>false, 'error'=>'server_error', 'message'=>$e->getMessage()], 500);
    }
}

}