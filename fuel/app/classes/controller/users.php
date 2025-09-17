<?php

use Fuel\Core\Session;
use Fuel\Core\Controller;
use Fuel\Core\View;
use Fuel\Core\Response;
use Fuel\Core\Input;

class Controller_Users extends Controller
{
    public function action_index()
    {
        // 任意：ユーザー一覧（管理用）
        $users = Model_User::find('all');
        $v = View::forge('users/index');
        $v->set('users', $users, false);
        return Response::forge($v);
    }

    // ログイン POST 受付（/users/create からでも /auth/login からでもOK）
        public function action_login()
        {
            if (Input::method() !== 'POST') {
                return Response::redirect('shifts');
            }
            $name  = trim((string)Input::post('name'));
            $color = trim((string)Input::post('color', '#000000'));
            if ($name === '') {
                Session::set_flash('error', '名前を入力してください');
                return Response::redirect('shifts');
            }
    
            // 既存検索 or 新規作成
            $user = Model_User::query()->where('name', $name)->get_one();
            if (!$user) {
                $user = Model_User::forge(['name'=>$name, 'color'=>$color]);
                $user->save();
            } else {
                // 色を更新したい時だけ
                if ($color) { $user->color = $color; $user->save(); }
            }
    
            Session::set('user_id', $user->id);   // ←ココがポイント
            return Response::redirect('shifts');  // 一覧へ
        }
    
        public function action_logout()
        {
            Session::delete('user_id');
            return Response::redirect('shifts');
        }
    }
