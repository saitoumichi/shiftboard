<?php

use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\View;

class Controller_Shifts extends Fuel\Core\Controller
{
    public function before()
    {
        parent::before();
        // ログイン不要ページがあればここで除外（今回はindexだけで分岐するのでOK）
    }

    public function action_index()
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            // 未ログイン → ログイン画面
            return Response::forge(View::forge('auth/login'));
        }
        // ログイン済 → 一覧を表示（JS が uid を使ってAPI叩く）
        $v = View::forge('shifts/index');
        $v->set('current_user_id', (int)$uid, false);   // ←ビューへ渡す
        return Response::forge($v);
    }

    public function action_create()
    {
        $this->require_login();
        return Response::forge(View::forge('shifts/create'));
    }

    public function action_view($id)
    {
        if (!Session::get('user_id')) Response::redirect('shifts');
        $v = View::forge('shifts/view');
        $v->set('current_user_id', (int)Session::get('user_id'), false);
        $v->set('shift_id', (int)$id, false);
        return Response::forge($v);
    }

    public function action_my()
    {
        // “自分のシフト”専用画面を作るなら
        if (!Session::get('user_id')) Response::redirect('shifts');
        $v = View::forge('shifts/my');
        $v->set('current_user_id', (int)Session::get('user_id'), false);
        return Response::forge($v);
    }

    private function require_login()
    {
        if (!Session::get('user_id')) {
            // 一覧のURLに戻す（そこが未ログイン時はログイン画面を出す）
            Response::redirect('shifts');
        }
    }
}