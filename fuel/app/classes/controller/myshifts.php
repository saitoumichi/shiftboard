<?php

use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\View;

class Controller_Myshifts extends \Fuel\Core\Controller
{
    public function action_index()
    {
        // 認証チェック
        $current_user_id = Session::get('user_id');
        if (!$current_user_id) {
            Session::set_flash('error', 'ログインが必要です');
            return Response::redirect('users/login');
        }

        $data = array(
            'title' => '自分のシフト',
            'subtitle' => '・ 参加予定のシフト一覧',
            'user_id' => $current_user_id
        );
        
        return View::forge('myshifts/index', $data);
    }
}
