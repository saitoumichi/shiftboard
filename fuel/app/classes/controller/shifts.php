<?php

/**
 * Shifts Controller
 * 
 * シフト管理用のコントローラー
 */
class Controller_Shifts extends Controller
{
    /**
     * シフト一覧表示
     */
    public function action_index()
    {
        $data = array(
            'title' => 'シフト一覧'
        );
        
        return Response::forge(View::forge('shifts/index', $data));
    }

    /**
     * シフト作成ページ表示
     */
    public function action_create()
    {
        $data = array(
            'title' => 'シフト作成'
        );
        
        return Response::forge(View::forge('shifts/create', $data));
    }

    /**
     * シフト詳細表示
     */
    public function action_view($id = null)
    {
        if (!$id) {
            throw new HttpNotFoundException();
        }
        
        $data = array(
            'title' => 'シフト詳細',
            'shift_id' => $id
        );
        
        return Response::forge(View::forge('shifts/view', $data));
    }
}