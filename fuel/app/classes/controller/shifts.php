<?php

class Controller_Shifts extends \Controller_Template
{
    /**
     * シフト一覧ページ (UI設計書 - シフト一覧)
     */
    public function action_index()
    {
        // Modelから全てのシフト情報を取得
        $shifts = Model_Shifts::find('all');
        
        // ビューにデータを渡す
        $this->template->title = 'シフト一覧';
        $this->template->content = \View::forge('shifts/index', array(
            'shifts' => $shifts,
        ));
    }

    /**
     * シフト作成ページ (UI設計書 - シフト登録)
     */
    public function action_create()
    {
        $this->template->title = 'シフト作成';
        $this->template->content = \View::forge('shifts/create');
    }

    /**
     * シフト詳細ページ (UI設計書 - シフト詳細)
     */
    public function action_view($id = null)
    {
        // IDからシフト詳細情報をモデル経由で取得
        $shift = Model_Shifts::find_by_id_with_assignments($id);
        
        if (!$shift) {
            throw new \HttpNotFoundException();
        }

        $this->template->title = 'シフト詳細';
        $this->template->content = \View::forge('shifts/view', array(
            'shift' => $shift,
        ));
    }
}