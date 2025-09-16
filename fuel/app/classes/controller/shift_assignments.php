<?php

class Controller_Shift_Assignments extends Fuel\Core\Controller
{
    public function action_index()
    {
        // シフト割り当て管理ページのメインアクション
        $data = array();
        
        // セッションからユーザー情報を取得
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/index', $data));
    }
    
    public function action_manage($shift_id = null)
    {
        // 特定のシフトの割り当て管理ページ
        if (!$shift_id) {
            return \Fuel\Core\Response::redirect('/shift_assignments');
        }
        
        $data = array();
        $data['shift_id'] = $shift_id;
        
        // セッションからユーザー情報を取得
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/manage', $data));
    }
    
    public function action_my_assignments()
    {
        // 自分の割り当て一覧ページ
        $data = array();
        
        // セッションからユーザー情報を取得
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/my_assignments', $data));
    }
    
    public function action_calendar()
    {
        // 割り当てカレンダー表示ページ
        $data = array();
        
        // セッションからユーザー情報を取得
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/calendar', $data));
    }
}
