<?php

class Controller_Shift_Assignment extends \Fuel\Core\Controller
{
    // シフト一覧 (カレンダー＋右側リスト想定)
    public function action_index()
    {
        // 近い順に取得（今日以降）
        $shifts = \Model_Shift::query()
            ->related('assignments')  // 参加者を一括取得（N+1回避）
            ->where('shift_date', '>=', date('Y-m-d'))
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->get();

        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shifts/index', [
            'shifts' => $shifts,
        ]));
    }

    // シフト作成（GET=フォーム表示 / POST=登録）
    public function action_create()
    {
        if (\Fuel\Core\Input::method() === 'POST') {
            // ORMのプロパティに沿って forge
            $shift = \Model_Shift::forge([
                'created_by'    => 1, // TODO: 認証導入後に置換
                'shift_date'    => \Fuel\Core\Input::post('shift_date'),
                'start_time'    => \Fuel\Core\Input::post('start_time'),
                'end_time'      => \Fuel\Core\Input::post('end_time'),
                'recruit_count' => (int)\Fuel\Core\Input::post('slot_count'),
                'free_text'     => \Fuel\Core\Input::post('note'),
            ]);
            try {
                $shift->save();
                \Fuel\Core\Session::set_flash('success', 'シフトを作成しました');
                return \Fuel\Core\Response::redirect('shifts/'.$shift->id);
             } catch (\Fuel\Core\Validation_Error $e) {
                 \Fuel\Core\Session::set_flash('error', $e->get_message());
             }
         }

        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shifts/create'));
    }

    // シフト詳細（左：参加者一覧 / 右：概要）
    public function action_view($id)
    {
        $shift = \Model_Shift::find($id, [
            'related' => [
                'assignments' => ['related' => ['user']], // ユーザーも一気に
            ],
        ]);
        if (!$shift) throw new \Fuel\Core\HttpNotFoundException;

        // 現在のユーザーIDを取得（認証未実装のため仮の値）
        $current_user_id = \Fuel\Core\Session::get('user_id', 1);
        
        // 既に参加しているかチェック
        $already_joined = false;
        foreach ($shift->assignments as $assignment) {
            if ($assignment->user_id == $current_user_id) {
                $already_joined = true;
                break;
            }
        }

        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shifts/view', [
            'shift'       => $shift,
            'assignments' => $shift->assignments,     // $a->user->name / color が使える
            'joined'      => $shift->joined_count(),
            'remaining'   => $shift->remaining(),
            'current_user_id'=> $current_user_id,
            'already_joined' => $already_joined,
        ]));
    }

    // 自分の割り当て一覧ページ
    public function action_my_assignments()
    {
        // 自分の割り当て一覧ページ
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        
        // 自分の参加中シフト一覧（必要に応じて並び替え）
        $assignments = \Model_Shift_Assignment::query()
            ->where('user_id', $user_id)
            ->where('status', 'assigned') // 取消分を除外したい場合
            ->related('shift')            // 関連シフトも一緒に
            ->order_by('id', 'desc')
            ->get();
        
        $data = array();
        $data['current_user_id'] = $user_id;
        $data['assignments'] = $assignments;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/my_assignments', $data));
    }

    // シフト割り当て管理ページのメインアクション
    public function action_assignments()
    {
        $data = array();
        
        // セッションからユーザー情報を取得
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/index', $data));
    }
    
    // 特定のシフトの割り当て管理ページ
    public function action_manage($shift_id = null)
    {
        if (!$shift_id) {
            return \Fuel\Core\Response::redirect('/shift/assignments');
        }
        
        $data = array();
        $data['shift_id'] = $shift_id;
        
        // セッションからユーザー情報を取得
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/manage', $data));
    }
    
    // 割り当てカレンダー表示ページ
    public function action_calendar()
    {
        $data = array();
        
        // セッションからユーザー情報を取得
        $user_id = \Fuel\Core\Session::get('user_id', 1); // 仮のユーザーID
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/calendar', $data));
    }
}
