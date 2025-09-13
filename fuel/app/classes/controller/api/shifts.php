<?php

class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';

    /**
     * シフト一覧取得
     */
    public function get_index()
    {
        try {
            // 全てのシフトをモデルから取得
            $shifts = Model_Shifts::get_all_with_assignments();
            return $this->response(Controller_Api_Common::successResponse($shifts, 'シフト一覧を取得しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト一覧の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト作成
     */
    public function action_create()
    {
        try {
            // POSTデータを取得
            $input = \Fuel\Core\Input::post() ?: json_decode(file_get_contents('php://input'), true);
            
            // バリデーションを実行（モデルのルールを使用）
            $val = Model_Shifts::validate('create');
            if (!$val->run($input)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($val->errors()), 400);
            }
            
            // モデルを使ってシフトを作成
            $shift = Model_Shifts::create_shift($input);
            if (!$shift) {
                throw new Exception('シフトの作成に失敗しました');
            }
            
            $formatted_shift = $shift->to_array_with_assignments();
            return $this->response(Controller_Api_Common::successResponse($formatted_shift, 'シフトを作成しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの作成に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト詳細取得
     */
    public function action_show($id)
    {
        try {
            // モデルを使ってシフト詳細を取得
            $shift = Model_Shifts::find_by_id_with_assignments($id);
            if (!$shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }
            return $this->response(Controller_Api_Common::successResponse($shift, 'シフト詳細を取得しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    // `action_update`, `action_delete`なども同様にモデルのメソッドを呼び出すように変更します。
    // 例:
    // public function action_update($id)
    // {
    //     try {
    //         if (!Model_Shifts::update_shift($id, \Fuel\Core\Input::post())) {
    //             throw new Exception('シフトの更新に失敗しました');
    //         }
    //         // 更新後のシフト情報を取得
    //         $shift = Model_Shifts::find_by_id_with_assignments($id);
    //         if (!$shift) {
    //             return $this->response(Controller_Api_Common::errorResponse('更新されたシフトが見つかりません', 404), 404);
    //         }
    //         return $this->response(Controller_Api_Common::successResponse($shift, 'シフトを更新しました'));
    //     } catch (Exception $e) {
    //         return $this->response(Controller_Api_Common::errorResponse('シフトの更新に失敗しました: ' . $e->getMessage(), 500), 500);
    //     }
    // }
}