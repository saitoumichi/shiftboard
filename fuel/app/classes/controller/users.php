<?php

class Controller_Users extends \Fuel\Core\Controller
{
    public function action_index() //メンバー一覧
    {
        $data = array(
            'title' => 'メンバー管理',
            'subtitle' => '・ メンバーの一覧表示・管理'
        );
        
        return \Fuel\Core\View::forge('users/index', $data);
    }

    public function action_create() //メンバー作成
    {
        $data = array(
            'title' => 'メンバー作成',
            'subtitle' => '・ 新しいメンバーを追加'
        );
        
        return \Fuel\Core\View::forge('users/create', $data);
    }

    public function action_view($id = null) //メンバー詳細
    {
        if (!$id) {
            throw new \Fuel\Core\HttpNotFoundException('メンバーIDが指定されていません');
        }
        
        $data = array(
            'title' => 'メンバー詳細',
            'subtitle' => '・ メンバーの詳細情報と参加シフト',
            'member_id' => $id
        );
        
        return \Fuel\Core\View::forge('users/view', $data);
    }
}
