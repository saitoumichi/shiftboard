<?php

/**
 * My Shifts Controller
 * 
 * 自分のシフト管理用のコントローラー
 */
class Controller_Myshifts extends Controller
{
    /**
     * 自分のシフト一覧表示
     */
    public function action_index()
    {
        $data = array(
            'title' => '自分のシフト'
        );
        
        return Response::forge(View::forge('myshifts/index', $data));
    }
}
