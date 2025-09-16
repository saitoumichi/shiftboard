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