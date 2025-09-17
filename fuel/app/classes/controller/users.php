<?php

use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\View;
use Model_User;

class Controller_Users extends \Fuel\Core\Controller
{
    public function action_create() // ← これが必要
    {
        error_log('action_create called, method: ' . Input::method());
        if (Input::method() === 'POST') {
            $name  = trim((string) Input::post('name', ''));
            $color = (string) Input::post('color', '#000000');
            error_log('POST data - name: ' . $name . ', color: ' . $color);

            if ($name === '') {
                // 超簡易バリデーション
                $users = Model_User::query()->order_by('name', 'asc')->get();
                $v = View::forge('users/login');
                $v->set('error', '名前は必須です', false);
                $v->set('users', $users);
                return Response::forge($v, 422);
            }

            // 既存があればそれでログイン、なければ作成
            $user = Model_User::query()->where('name', $name)->get_one();
            if (!$user) {
                $user = Model_User::forge([
                    'name'       => $name,
                    'color'      => $color ?: '#000000',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $user->save();
            }

            // セッションに保存してログイン扱い
            Session::set('user_id', (int) $user->id);

            // 一覧へ
            return Response::redirect('shifts');
        }

        // GET: フォーム表示
        // 既存のユーザー一覧を取得
        try {
            // デバッグ: Model_Userが存在するかチェック
            if (!class_exists('Model_User')) {
                error_log('Model_User class does not exist');
                $users = array();
            } else {
                error_log('Model_User class exists, attempting query...');
                $users = Model_User::query()->order_by('name', 'asc')->get();
                error_log('Query result type: ' . gettype($users));
                error_log('Query result count: ' . (is_array($users) ? count($users) : 'not array'));
                
                if (!$users) {
                    $users = array();
                }
            }
        } catch (Exception $e) {
            error_log('Error fetching users: ' . $e->getMessage());
            error_log('Error trace: ' . $e->getTraceAsString());
            $users = array();
        }
        
        $view = View::forge('users/login');
        $view->set('users', $users);
        
        
        return Response::forge($view);
    }

    public function action_login()  // GET フォーム表示
    {
        // user_idパラメータが指定されている場合は直接ログイン
        $user_id = Input::get('user_id');
        if ($user_id) {
            $user = Model_User::find($user_id);
            if ($user) {
                Session::set('user_id', (int)$user->id);
                return Response::redirect('shifts');
            }
        }
        
        // 既存のユーザー一覧を取得
        try {
            $users = Model_User::query()->order_by('name', 'asc')->get();
            if (!$users) {
                $users = array();
            }
        } catch (Exception $e) {
            error_log('Error fetching users in login: ' . $e->getMessage());
            $users = array();
        }
        
        $view = View::forge('users/login');
        $view->set('users', $users);
        
        
        return Response::forge($view);
    }

    public function post_login()    // POST 送信先
    {
        $name  = trim(Input::post('name', ''));
        $color = trim(Input::post('color', '#000000'));

        if ($name === '') {
            $users = Model_User::query()->order_by('name', 'asc')->get();
            $view = View::forge('users/login');
            $view->set('error', '名前は必須です', false);
            $view->set('users', $users);
            return Response::forge($view);
        }

        // 既存 or 新規
        $user = \Model_User::query()->where('name', $name)->get_one();
        if (!$user) {
            $user = \Model_User::forge(['name'=>$name, 'color'=>$color]);
            $user->save();
        }

        Session::set('user_id', (int)$user->id);
        
        // デバッグ用ログ
        error_log('ログイン成功 - ユーザーID: ' . (int)$user->id);
        error_log('セッション設定後 - user_id: ' . Session::get('user_id'));

        // ログイン後は一覧へ
        return Response::redirect('shifts');
    }

    public function action_logout()
    {
        \Fuel\Core\Session::destroy(); // セッションを完全削除
        \Fuel\Core\Response::redirect('users/login'); // ログイン画面へ戻す
    }

    public function action_delete($id = null)
    {
        if (!$id) {
            return Response::redirect('users/login');
        }

        $user = Model_User::find($id);
        if (!$user) {
            return Response::redirect('users/login');
        }

        try {
            // トランザクション開始
            \Fuel\Core\DB::start_transaction();

            // 1. ユーザーが作成したシフトを削除（shift_assignmentsも自動削除される）
            \Fuel\Core\DB::query("DELETE FROM shifts WHERE created_by = " . (int)$id);

            // 2. ユーザーが参加しているシフト割り当てを削除
            \Fuel\Core\DB::query("DELETE FROM shift_assignments WHERE user_id = " . (int)$id);

            // 3. ユーザーを削除
            $user->delete();

            // トランザクションコミット
            \Fuel\Core\DB::commit_transaction();

        } catch (Exception $e) {
            // エラーが発生した場合はロールバック
            \Fuel\Core\DB::rollback_transaction();
            throw $e;
        }

        // ログイン画面にリダイレクト
        return Response::redirect('users/login');
    }
    
    public function action_create_test_users()  // テストユーザー作成
    {
        try {
            // 既存のテストユーザーをチェック
            $existingUsers = Model_User::query()->where('name', 'in', ['田中太郎', '佐藤花子', '鈴木一郎'])->get();
            if (count($existingUsers) > 0) {
                return Response::forge(json_encode([
                    'ok' => false,
                    'message' => 'テストユーザーは既に存在します'
                ]), 400, ['Content-Type' => 'application/json']);
            }
            
            // テストユーザーを作成
            $testUsers = [
                ['name' => '田中太郎', 'color' => '#ff6b6b'],
                ['name' => '佐藤花子', 'color' => '#4ecdc4'],
                ['name' => '鈴木一郎', 'color' => '#45b7d1']
            ];
            
            foreach ($testUsers as $userData) {
                $user = Model_User::forge([
                    'name' => $userData['name'],
                    'color' => $userData['color'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $user->save();
            }
            
            return Response::forge(json_encode([
                'ok' => true,
                'message' => 'テストユーザーを作成しました'
            ]), 200, ['Content-Type' => 'application/json']);
            
        } catch (Exception $e) {
            return Response::forge(json_encode([
                'ok' => false,
                'message' => 'エラー: ' . $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

}