<?php

class Controller_Dbtest2 extends Controller
{
    public function action_index()
    {
        try {
            // FuelPHPの設定を確認
            $config = Config::get('db.default');
            
            // データベース接続をテスト
            $connection = Database_Connection::instance('default');
            
            // 直接クエリを実行
            $result = DB::query("SELECT COUNT(*) as count FROM shifts")->execute();
            $count = $result->get('count');
            
            // シフト一覧を取得
            $shifts = DB::query("SELECT * FROM shifts LIMIT 3")->execute()->as_array();
            
            $response = array(
                'success' => true,
                'config' => $config,
                'connection_type' => get_class($connection),
                'shifts_count' => $count,
                'shifts_sample' => $shifts
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        }
        
        $response_obj = Response::forge(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $response_obj->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response_obj;
    }
}
