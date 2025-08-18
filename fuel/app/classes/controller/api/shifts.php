<?php

class Controller_Api_Shifts extends Controller_Rest
{
    protected $format = "json";

    public function before()
    {
        parent::before();
        if (\Input::method() === "POST") {
            \Security::check_token();
        }
    }

    public function get_list()
    {
        $rows = \DB::select("*")->from("shifts")
            ->order_by("shift_date","asc")->order_by("start_time","asc")
            ->execute()->as_array();
        return $this->response(["items"=>$rows], 200);
    }

    public function post_create()
    {
        $p = \Input::post();
        foreach (["shift_date","start_time","end_time"] as $k) {
            if (empty($p[$k])) return $this->response(["error"=>"$k required"], 400);
        }
        if (strcmp($p["start_time"], $p["end_time"]) >= 0) {
            return $this->response(["error"=>"start_time < end_time required"], 400);
        }
        list($id,) = \DB::insert("shifts")->set([
            "shift_date" => $p["shift_date"],
            "start_time" => $p["start_time"],
            "end_time"   => $p["end_time"],
            "slot_count" => max(1, (int)($p["slot_count"] ?? 1)),
            "note"       => \Security::xss_clean($p["note"] ?? null),
        ])->execute();
        return $this->response(["id"=>$id], 201);
    }
}
