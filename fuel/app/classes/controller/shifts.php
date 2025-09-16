<?php

class Controller_Shifts extends \Fuel\Core\Controller
{
    // シフト一覧 (カレンダー＋右側リスト想定)
    public function action_index()
    {
        // 近い順に取得（今日以降）
        $shifts = \Model_Shift::query()
            ->related('assignments')                 // 参加者を一括取得（N+1回避）
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

        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shifts/view', [
            'shift'       => $shift,
            'assignments' => $shift->assignments,     // $a->user->name / color が使える
            'joined'      => $shift->joined_count(),
            'remaining'   => $shift->remaining(),
        ]));
    }
}