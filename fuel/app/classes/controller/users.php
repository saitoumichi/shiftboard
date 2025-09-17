<?php

use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\View;

class Controller_Users extends \Fuel\Core\Controller
{
    public function action_create() // ← これが必要
    {
        if (Input::method() === 'POST') {
            $name  = trim((string) Input::post('name', ''));
            $color = (string) Input::post('color', '#000000');

            if ($name === '') {
                // 超簡易バリデーション
                $v = View::forge('users/create');
                $v->set('error', '名前は必須です', false);
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
        return Response::forge(View::forge('users/create'));
    }

    public function action_login()  // GET フォーム表示
    {
        return Response::forge(View::forge('users/login'));
    }

    public function post_login()    // POST 送信先
    {
        $name  = trim(Input::post('name', ''));
        $color = trim(Input::post('color', '#000000'));

        if ($name === '') {
            return Response::forge(View::forge('users/login')->set('error', '名前は必須です', false));
        }

        // 既存 or 新規
        $user = \Model_User::query()->where('name', $name)->get_one();
        if (!$user) {
            $user = \Model_User::forge(['name'=>$name, 'color'=>$color]);
            $user->save();
        }

        Session::set('user_id', (int)$user->id);

        // ログイン後は一覧へ
        return Response::redirect('shifts');
    }

    public function action_logout()
    {
        \Fuel\Core\Session::destroy(); // セッションを完全削除
        \Fuel\Core\Response::redirect('users/login'); // ログイン画面へ戻す
    }

}