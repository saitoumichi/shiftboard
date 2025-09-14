<?php

use Fuel\Core\View;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Session;

class Controller_Shifts extends \Controller
{
    // シフト一覧 (カレンダー＋右側リスト想定)
    public function action_index()
    {
        // 近い順に取得（今日以降）
        $shifts = \DB::query("
            SELECT s.*,
                   COUNT(CASE WHEN sa.status != 'cancelled' THEN sa.user_id END) AS joined_count,
                   (s.recruit_count - COUNT(CASE WHEN sa.status != 'cancelled' THEN sa.user_id END)) AS remaining
            FROM shifts s
            LEFT JOIN shift_assignments sa ON sa.shift_id = s.id
            WHERE s.shift_date >= CURRENT_DATE
            GROUP BY s.id
            ORDER BY s.shift_date ASC, s.start_time ASC
        ")->execute()->as_array();

        return Response::forge(View::forge('shifts/index', [
            'shifts' => $shifts,
        ]));
    }

    // シフト作成（GET=フォーム表示 / POST=登録）
    public function action_create()
    {
        if (Input::method() === 'POST') {
            $val = \Validation::forge();
            $val->add('shift_date', '日付')->add_rule('required')->add_rule('valid_date', 'Y-m-d');
            $val->add('start_time', '開始')->add_rule('required');
            $val->add('end_time',   '終了')->add_rule('required');
            $val->add('recruit_count', '募集')->add_rule('required')->add_rule('valid_string', ['numeric']);

            if ($val->run()) {
                list($id,) = \DB::insert('shifts')->set([
                    'created_by'    => 1, // TODO: 認証導入後に置き換え
                    'shift_date'    => Input::post('shift_date'),
                    'start_time'    => Input::post('start_time'),
                    'end_time'      => Input::post('end_time'),
                    'recruit_count' => (int)Input::post('recruit_count'),
                    'free_text'     => Input::post('free_text'),
                    'created_at'    => \DB::expr('CURRENT_TIMESTAMP'),
                ])->execute();

                Session::set_flash('success', 'シフトを作成しました');
                return \Response::redirect('shifts/'.$id);
            }

            Session::set_flash('error', $val->show_errors());
        }

        return Response::forge(View::forge('shifts/create'));
    }

    // シフト詳細（左：参加者一覧 / 右：概要）
    public function action_view($id)
    {
        $shift = \DB::select()->from('shifts')->where('id', $id)->execute()->current();
        if (!$shift) {
            throw new \HttpNotFoundException;
        }

        $assignments = \DB::query("
            SELECT sa.user_id, sa.status, sa.self_word, u.name, u.color
            FROM shift_assignments sa
            JOIN users u ON u.id = sa.user_id
            WHERE sa.shift_id = :id AND sa.status != 'cancelled'
            ORDER BY sa.created_at ASC
        ")->parameters(['id' => $id])->execute()->as_array();

        $joined = count($assignments);
        $remaining = max(0, (int)$shift['recruit_count'] - $joined);

        return Response::forge(View::forge('shifts/view', [
            'shift'       => $shift,
            'assignments' => $assignments,
            'joined'      => $joined,
            'remaining'   => $remaining,
        ]));
    }
}