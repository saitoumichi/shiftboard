<?php

class Controller_Users extends \Fuel\Core\Controller
{
    public function action_index()
    {
        $data = array(
            'title' => 'メンバー管理',
            'subtitle' => '・ メンバーの一覧表示・管理'
        );
        
        return \Fuel\Core\View::forge('users/index', $data);
    }

    public function action_create()
    {
        $data = array(
            'title' => 'メンバー作成',
            'subtitle' => '・ 新しいメンバーを追加'
        );
        
        return \Fuel\Core\View::forge('users/create', $data);
    }
    
    /**
     * メンバー詳細画面
     */
    public function action_view($id = null)
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
