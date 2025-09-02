<?php

class Controller_Dbtest extends Controller
{
    public function action_index()
    {
        try {
            // 直接MySQL接続テスト
            $mysqli = new mysqli('127.0.0.1', 'app', 'app_pass', 'shiftboard', 13306);
            
            if ($mysqli->connect_error) {
                throw new Exception('MySQL接続エラー: ' . $mysqli->connect_error);
            }
            
            $result = $mysqli->query("SELECT COUNT(*) as count FROM shifts");
            $row = $result->fetch_assoc();
            $direct_count = $row['count'];
            
            $mysqli->close();
            
            // FuelPHP DB接続テスト
            $fuel_shifts = DB::select()
                ->from('shifts')
                ->execute()
                ->as_array();
            
            $fuel_count = count($fuel_shifts);
            
            $response = array(
                'success' => true,
                'direct_mysql_count' => $direct_count,
                'fuelphp_count' => $fuel_count,
                'connection_status' => 'OK',
                'fuel_shifts' => $fuel_shifts
            );
            
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage(),
                'connection_status' => 'ERROR'
            );
        }
        
        $response_obj = Response::forge(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $response_obj->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response_obj;
    }
}
