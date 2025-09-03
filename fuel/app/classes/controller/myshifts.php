<?php

/**
 * My Shifts Controller
 * 
 * 自分のシフト管理用のコントローラー
 */
class Controller_Myshifts extends \Fuel\Core\Controller
{
    /**
     * 自分のシフト一覧表示
     */
    public function action_index()
    {
        $data = array(
            'title' => '自分のシフト'
        );
        
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('myshifts/index', $data));
    }
}
