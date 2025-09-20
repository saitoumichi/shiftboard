<?php

use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\View;

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
                $users = \Model_User::query()->order_by('name', 'asc')->get();
                $v = View::forge('users/login');
                $v->set('error', '名前は必須です', false);
                $v->set('users', $users);
                return Response::forge($v, 422);
            }

            // 既存があればそれでログイン、なければ作成
            $user = \Model_User::query()->where('name', $name)->get_one();
            if (!$user) {
                $user = \Model_User::forge([
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
            $users = \Model_User::query()->order_by('name', 'asc')->get();
            if (!$users) {
                $users = array();
            }
        } catch (Exception $e) {
            error_log('Error fetching users: ' . $e->getMessage());
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
            $user = \Model_User::find($user_id);
            if ($user) {
                Session::set('user_id', (int)$user->id);
                return Response::redirect('shifts');
            }
        }
        
        // 既存のユーザー一覧を取得
        try {
            $users = \Model_User::query()->order_by('name', 'asc')->get();
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
            $users = \Model_User::query()->order_by('name', 'asc')->get();
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
        error_log('セッションID: ' . Session::key());

        // ログイン後は一覧へ
        return Response::redirect('shifts');
    }

    public function action_logout()
    {
        \Fuel\Core\Session::destroy(); // セッションを完全削除
        \Fuel\Core\Response::redirect('users/login'); // ログイン画面へ戻す
    }


    public function action_delete($user_id = null)
    {
        error_log('action_delete called, user_id: ' . ($user_id ?: 'null'));
        
        if (!$user_id) {
            error_log('action_delete - ユーザーIDが指定されていません');
            return Response::forge('ユーザーIDが指定されていません', 400);
        }

        try {
            // ユーザーを取得
            $user = \Model_User::query()->where('id', $user_id)->get_one();
            if (!$user) {
                error_log('action_delete - ユーザーが見つかりません: ' . $user_id);
                return Response::forge('ユーザーが見つかりません', 404);
            }

            $user_name = $user->name;
            error_log('action_delete - 削除対象ユーザー: ' . $user_name . ' (ID: ' . $user_id . ')');

            // トランザクション開始
            \Fuel\Core\DB::start_transaction();

            try {
                // ユーザーが作成したシフトを削除（関連するshift_assignmentsも自動削除される）
                $created_shifts = \Model_Shift::query()->where('created_by', $user_id)->get();
                foreach ($created_shifts as $shift) {
                    error_log('action_delete - シフト削除: ' . $shift->id);
                    $shift->delete();
                }

                // ユーザーが参加しているシフト参加を削除
                $assignments = \Model_Shift_Assignment::query()->where('user_id', $user_id)->get();
                foreach ($assignments as $assignment) {
                    error_log('action_delete - シフト参加削除: ' . $assignment->id);
                    $assignment->delete();
                }

                // ユーザーを削除
                error_log('action_delete - ユーザー削除実行: ' . $user_id);
                $user->delete();

                // トランザクションコミット
                \Fuel\Core\DB::commit_transaction();
                
                error_log('action_delete - 削除完了: ' . $user_name);
                
                // ログインページにリダイレクト
                return Response::redirect('/users/login', 'refresh');
                
            } catch (Exception $e) {
                // トランザクションロールバック
                \Fuel\Core\DB::rollback_transaction();
                error_log('action_delete - トランザクションエラー: ' . $e->getMessage());
                throw $e;
            }

        } catch (Exception $e) {
            error_log('action_delete - エラー: ' . $e->getMessage());
            return Response::forge('ユーザーの削除に失敗しました: ' . $e->getMessage(), 500);
        }
    }

}