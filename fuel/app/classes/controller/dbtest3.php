<?php

class Controller_Dbtest3 extends Controller
{
    public function action_index()
    {
        try {
            // 新しいデータベース接続を作成
            $db_config = array(
                'type' => 'mysqli',
                'connection' => array(
                    'hostname' => '127.0.0.1',
                    'port' => '13306',
                    'database' => 'shiftboard',
                    'username' => 'app',
                    'password' => 'app_pass',
                    'persistent' => false,
                ),
                'charset' => 'utf8mb4',
            );
            
            // 新しい接続を作成
            $connection = Database_Connection::instance('test', $db_config);
            
            // クエリを実行
            $result = $connection->query(1, 'SELECT COUNT(*) as count FROM shifts', false);
            $count = $result->get('count');
            
            // シフト一覧を取得
            $shifts = $connection->query(1, 'SELECT * FROM shifts LIMIT 3', false)->as_array();
            
            $response = array(
                'success' => true,
                'shifts_count' => $count,
                'shifts_sample' => $shifts,
                'connection_class' => get_class($connection)
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        $response_obj = Response::forge(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $response_obj->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response_obj;
    }
}
