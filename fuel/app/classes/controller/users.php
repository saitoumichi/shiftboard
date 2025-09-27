<?php

use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\View;
use Fuel\Core\DB;

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
        // 既存のユーザー一覧を取得（DB::selectで最適化）
        try {
            $query = DB::select('id', 'name', 'color', 'created_at')
                ->from('users');
            $query->order_by('name', 'asc');
            $result = $query->execute();
            $users = $result->as_array();
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
        
        // 既存のユーザー一覧を取得（DB::selectで最適化）
        try {
            $query = DB::select('id', 'name', 'color', 'created_at')
                ->from('users');
            $query->order_by('name', 'asc');
            $result = $query->execute();
            $users = $result->as_array();
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
        Session::destroy(); // セッションを完全削除
        \Fuel\Core\Response::redirect('users/login'); // ログイン画面へ戻す
    }

    public function action_delete($user_id = null)
    {
        error_log('action_delete called, user_id: ' . ($user_id ?: 'null'));
        
        // 入力値の検証とサニタイズ
        if (!$user_id || !is_numeric($user_id)) {
            error_log('action_delete - 無効なユーザーID: ' . $user_id);
            return Response::forge('無効なユーザーIDです', 400);
        }
        
        $user_id = (int)$user_id; // 整数にキャストしてサニタイズ

        try {
            // クエリビルダーを使用してユーザーを安全に取得
            $user = DB::select('id', 'name')
                ->from('users')
                ->where('id', $user_id)
                ->execute()
                ->current();
                
            if (!$user) {
                error_log('action_delete - ユーザーが見つかりません: ' . $user_id);
                return Response::forge('ユーザーが見つかりません', 404);
            }

            $user_name = $user['name'];
            error_log('action_delete - 削除対象ユーザー: ' . $user_name . ' (ID: ' . $user_id . ')');

            // トランザクション開始
            DB::start_transaction();

            try {
                // クエリビルダーを使用して関連データを安全に削除
                
                // 1. ユーザーが作成したシフトの関連するshift_assignmentsを削除
                DB::delete('shift_assignments')
                    ->where('shift_id', 'IN', 
                        DB::select('id')
                            ->from('shifts')
                            ->where('created_by', $user_id)
                    )
                    ->execute();
                
                // 2. ユーザーが作成したシフトを削除
                $deleted_shifts = DB::delete('shifts')
                    ->where('created_by', $user_id)
                    ->execute();
                error_log('action_delete - 削除されたシフト数: ' . $deleted_shifts);

                // 3. ユーザーが参加しているシフト参加を削除
                $deleted_assignments = DB::delete('shift_assignments')
                    ->where('user_id', $user_id)
                    ->execute();
                error_log('action_delete - 削除された参加数: ' . $deleted_assignments);

                // 4. ユーザーを削除
                $deleted_users = DB::delete('users')
                    ->where('id', $user_id)
                    ->execute();
                error_log('action_delete - 削除されたユーザー数: ' . $deleted_users);

                // トランザクションコミット
                DB::commit_transaction();
                
                error_log('action_delete - 削除完了: ' . $user_name);
                
                // ログインページにリダイレクト
                return Response::redirect('/users/login', 'refresh');
                
            } catch (Exception $e) {
                // トランザクションロールバック
                DB::rollback_transaction();
                error_log('action_delete - トランザクションエラー: ' . $e->getMessage());
                throw $e;
            }

        } catch (Exception $e) {
            error_log('action_delete - エラー: ' . $e->getMessage());
            return Response::forge('ユーザーの削除に失敗しました: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ユーザー編集画面を表示
     */
    public function action_edit($user_id = null)
    {
        if (!$user_id) {
            return Response::forge('ユーザーIDが指定されていません', 400);
        }

        $user = \Model_User::find($user_id);
        if (!$user) {
            return Response::forge('ユーザーが見つかりません', 404);
        }

        $view = View::forge('users/edit');
        $view->set('user', $user);
        return Response::forge($view);
    }

    /**
     * ユーザー情報を更新
     */
    public function action_update($user_id = null)
    {
        if (Input::method() !== 'POST') {
            return Response::forge('POSTメソッドでアクセスしてください', 405);
        }

        if (!$user_id) {
            return Response::forge('ユーザーIDが指定されていません', 400);
        }

        $user = \Model_User::find($user_id);
        if (!$user) {
            return Response::forge('ユーザーが見つかりません', 404);
        }

        $name = trim(Input::post('name', ''));
        $color = trim(Input::post('color', '#000000'));

        // バリデーション
        if ($name === '') {
            $view = View::forge('users/edit');
            $view->set('user', $user);
            $view->set('error', '名前は必須です', false);
            return Response::forge($view, 422);
        }


        try {
            // ユーザー情報を更新
            $user->name = $name;
            $user->color = $color;
            $user->updated_at = date('Y-m-d H:i:s');
            $user->save();

            error_log('ユーザー更新完了: ' . $name . ' (ID: ' . $user_id . ')');
            
            // ログイン画面にリダイレクト
            return Response::redirect('users/login');
            
        } catch (Exception $e) {
            error_log('ユーザー更新エラー: ' . $e->getMessage());
            $view = View::forge('users/edit');
            $view->set('user', $user);
            $view->set('error', 'ユーザー情報の更新に失敗しました', false);
            return Response::forge($view, 500);
        }
    }

}