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
        if (\Fuel\Core\Input::method() === 'POST') {
            // POST処理：シフト作成
            try {
                $shift_date = \Fuel\Core\Input::post('shift_date');
                $start_time = \Fuel\Core\Input::post('start_time');
                $end_time = \Fuel\Core\Input::post('end_time');
                $slot_count = \Fuel\Core\Input::post('slot_count', 1);
                $note = \Fuel\Core\Input::post('note', '');
                
                // バリデーション
                if (empty($shift_date) || empty($start_time) || empty($end_time)) {
                    \Fuel\Core\Session::set_flash('error', '必須項目を入力してください');
                    return \Fuel\Core\Response::redirect(\Fuel\Core\Uri::create('shifts/create'));
                }
                
                if ($start_time >= $end_time) {
                    \Fuel\Core\Session::set_flash('error', '終了時間は開始時間より後にしてください');
                    return \Fuel\Core\Response::redirect(\Fuel\Core\Uri::create('shifts/create'));
                }
                
                // データベースに保存
                $pdo = new PDO('mysql:host=localhost;dbname=shiftboard;charset=utf8mb4', 'root', 'password');
                $stmt = $pdo->prepare('INSERT INTO shifts (shift_date, start_time, end_time, slot_count, note) VALUES (?, ?, ?, ?, ?)');
                $result = $stmt->execute([$shift_date, $start_time, $end_time, $slot_count, $note]);
                
                if ($result) {
                    \Fuel\Core\Session::set_flash('success', 'シフトが作成されました');
                    return \Fuel\Core\Response::redirect(\Fuel\Core\Uri::create('shifts'));
                } else {
                    \Fuel\Core\Session::set_flash('error', 'シフトの作成に失敗しました');
                    return \Fuel\Core\Response::redirect(\Fuel\Core\Uri::create('shifts/create'));
                }
                
            } catch (Exception $e) {
                \Fuel\Core\Session::set_flash('error', 'エラーが発生しました: ' . $e->getMessage());
                return \Fuel\Core\Response::redirect(\Fuel\Core\Uri::create('shifts/create'));
            }
        }
        
        // GET処理：フォーム表示
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