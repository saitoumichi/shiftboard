<?php

use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\View;

class Controller_Shifts extends \Fuel\Core\Controller
{
    public function before()
    {
        parent::before();
        Session::instance(); // セッション初期化を確実に
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
            return Response::forge(View::forge('users/login')); // 未ログイン→ログイン画面
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
        $this->require_login();
        $v = View::forge('shifts/view');
        $v->set('current_user_id', (int)Session::get('user_id'), false);
        $v->set('shift_id', (int)$id, false);
        return Response::forge($v);
    }

    public function action_my() //自分のシフト
    {
        if ( ! \Fuel\Core\Session::get('user_id')) {
            \Fuel\Core\Response::redirect('users/login');
        }
        $v = \Fuel\Core\View::forge('shifts/my');
        $v->set('current_user_id', (int)\Fuel\Core\Session::get('user_id'), false);
        return \Fuel\Core\Response::forge($v);
    }
}