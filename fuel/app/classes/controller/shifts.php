<?php

class Controller_Shifts extends Controller_Template
{
    public $template = "template";

    public function action_index()
    {
        $rows = \DB::select("*")->from("shifts")
            ->order_by("shift_date","asc")->order_by("start_time","asc")
            ->execute()->as_array();

        $this->template->title   = "シフト一覧";
        $this->template->content = View::forge("shifts/index", ["shifts" => $rows]);
    }

    public function action_create()
    {
        $this->template->title   = "シフト作成";
        $this->template->content = View::forge("shifts/create", [
            "csrf" => \Security::fetch_token(),
        ]);
    }
}
