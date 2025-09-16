<?php
class Controller_Api_Users extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    public function before()
    {
        parent::before();
        // レスポンスは常にJSON
        header('Content-Type: application/json; charset=UTF-8');
    }

    // GET /api/users
    public function get_index()
    {
        $users = \Model_User::find('all');
        return $this->response([
            'ok' => true,
            'users' => $users,
        ]);
    }

    // POST /api/users
    public function post_index()
    {
        // JSON を安全に読む（x-www-form-urlencoded にもフォールバック）
        $raw  = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $in   = is_array($json) ? $json : \Fuel\Core\Input::post();

        // ---- Validation ----
        $val = \Fuel\Core\Validation::forge();
        $val->add('name', 'User Name')
            ->add_rule('required')
            ->add_rule('min_length', 1)
            ->add_rule('max_length', 100);

        $val->add('color', 'Color')
            ->add_rule('required')
            ->add_rule('match_pattern', '/^#[0-9A-Fa-f]{6}$/'); // カラーコード形式

        if ( ! $val->run($in)) {
            // 422 Unprocessable Entity で返す
            return $this->response([
                'ok'     => false,
                'errors' => $val->error()
            ], 422);
        }

        try {
            // 保存
            $user = \Model_User::forge([
                'name'  => $in['name'],
                'color' => $in['color'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $user->save();

            return $this->response([
                'ok'    => true,
                'user'  => $user
            ], 201);

        } catch (\Exception $e) {
            // DBエラーはここで拾える
            return $this->response([
                'success' => false,
                'error'   => 'server_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}