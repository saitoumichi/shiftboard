<?php

use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\View;
use Fuel\Core\DB;

class Controller_Shifts extends \Fuel\Core\Controller
{
    public function before()
    {
        parent::before();
        try {
            Session::instance(); // セッション初期化を確実に
        } catch (Exception $e) {
            // セッションエラーの場合は新しいセッションを開始
            error_log('Session error: ' . $e->getMessage());
            Session::destroy();
            Session::instance();
        }
    }

    private function require_login()
    {
        if (!Session::get('user_id')) {
            Response::redirect('users/login');
        }
    }

    public function action_index()  //未ログインならusers/loginが返るはず！（ログイン済みならそのまま）
    {
        $uid = Session::get('user_id');
        error_log('shifts/index - セッションuser_id: ' . ($uid ?: 'null'));
        error_log('shifts/index - セッションID: ' . Session::key());
        if (!$uid) {
            error_log('shifts/index - 未ログインのためログイン画面にリダイレクト');
            
            // 既存のユーザー一覧を取得（DB::selectで最適化）
            try {
                $users = DB::select('id', 'name', 'color', 'created_at')
                    ->from('users')
                    ->order_by('name', 'asc')
                    ->execute()
                    ->as_array();
            } catch (Exception $e) {
                error_log('Error fetching users in shifts/index: ' . $e->getMessage());
                $users = array();
            }
            
            $view = View::forge('users/login');
            $view->set('users', $users);
            return Response::forge($view); // 未ログイン→ログイン画面（既存ユーザー付き）
        }
        $v = View::forge('shifts/index');
        $v->set('current_user_id', (int)Session::get('user_id'), false); // ← ここが超重要
        error_log('shifts/index - current_user_idを設定: ' . (int)Session::get('user_id'));
        return Response::forge($v);
    }

    public function action_create()  //作成画面
    {
        $this->require_login();
        $v = View::forge('shifts/create');
        $v->set('current_user_id', (int)Session::get('user_id'), false);
        return Response::forge($v);
    }

    public function action_view($id)  //詳細画面
    {
        error_log('action_view called with id: ' . $id);
        $this->require_login();
        $v = View::forge('shifts/view');
        $v->set('current_user_id', (int)Session::get('user_id'), false);
        $v->set('shift_id', (int)$id, false);
        error_log('shift_id set to: ' . (int)$id);
        return Response::forge($v);
    }

    public function action_my() //自分のシフト
    {
        if ( ! Session::get('user_id')) {
            \Fuel\Core\Response::redirect('users/login');
        }
        $v = \Fuel\Core\View::forge('shifts/my');
        $v->set('current_user_id', (int)Session::get('user_id'), false);
        return \Fuel\Core\Response::forge($v);
    }

}