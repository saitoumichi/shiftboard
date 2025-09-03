<?php

/**
 * メンバー管理用のコントローラー
 */
class Controller_Members extends Controller
{
    /**
     * メンバー一覧画面
     */
    public function action_index()
    {
        $data = array(
            'title' => 'メンバー管理',
            'subtitle' => '・ メンバーの一覧表示・管理'
        );
        
        return View::forge('members/index', $data);
    }
    
    /**
     * メンバー作成画面
     */
    public function action_create()
    {
        $data = array(
            'title' => 'メンバー作成',
            'subtitle' => '・ 新しいメンバーを追加'
        );
        
        return View::forge('members/create', $data);
    }
    
    /**
     * メンバー詳細画面
     */
    public function action_view($id = null)
    {
        if (!$id) {
            throw new HttpNotFoundException('メンバーIDが指定されていません');
        }
        
        $data = array(
            'title' => 'メンバー詳細',
            'subtitle' => '・ メンバーの詳細情報と参加シフト',
            'member_id' => $id
        );
        
        return View::forge('members/view', $data);
    }
}
