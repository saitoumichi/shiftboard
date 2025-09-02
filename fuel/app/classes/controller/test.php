<?php

class Controller_Test extends Controller
{
    public function action_index()
    {
        try {
            // データベース接続テスト
            $shifts = DB::select()
                ->from('shifts')
                ->execute()
                ->as_array();
            
            $response = array(
                'success' => true,
                'shifts_count' => count($shifts),
                'shifts' => $shifts
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        $response_obj = Response::forge(json_encode($response));
        $response_obj->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response_obj;
    }
}
