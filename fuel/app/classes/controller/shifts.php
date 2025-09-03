<?php

/**
 * Shifts Controller
 * 
 * シフト管理用のコントローラー
 */
class Controller_Shifts extends \Fuel\Core\Controller
{
    /**
     * シフト一覧表示
     */
    public function action_index()
    {
        $data = array(
            'title' => 'シフト一覧'
        );
        
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shifts/index', $data));
    }

    /**
     * シフト作成ページ表示
     */
    public function action_create()
    {
        $data = array(
            'title' => 'シフト作成'
        );
        
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shifts/create', $data));
    }

    /**
     * シフト詳細表示
     */
    public function action_view($id = null)
    {
        if (!$id) {
            throw new \Fuel\Core\HttpNotFoundException();
        }
        
        $data = array(
            'title' => 'シフト詳細',
            'shift_id' => $id
        );
        
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shifts/view', $data));
    }
}