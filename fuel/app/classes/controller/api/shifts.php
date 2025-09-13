<?php

/**
 * API Shifts Controller
 * シフト管理用のAPIコントローラー
 */
class Controller_Api_Shifts extends \Fuel\Core\Controller_Rest
{
    protected $format = 'json';
    
    /**
     * シフト一覧取得
     */
    public function get_index()
    {
        try {
            $shifts = Model_Shifts::get_all_with_assignments();
            return $this->response(['items' => $shifts]);
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
            $input = \Fuel\Core\Input::post() ?: json_decode(file_get_contents('php://input'), true);

            $val = Model_Shifts::validate('create');
            if (!$val->run($input)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($val->errors()), 400);
            }
            
            $shift = Model_Shifts::forge($input);
            if (!$shift->save()) {
                throw new Exception('シフトの保存に失敗しました');
            }

            $formatted_shift = Model_Shifts::find_by_id_with_assignments($shift->id);
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
            $shift = Model_Shifts::find_by_id_with_assignments($id);
            if (!$shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }
            return $this->response(Controller_Api_Common::successResponse($shift, 'シフト詳細を取得しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト更新
     */
    public function action_update($id)
    {
        try {
            $shift = Model_Shifts::find($id);
            if (!$shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }
            
            $input = \Fuel\Core\Input::put() ?: json_decode(file_get_contents('php://input'), true);

            $val = Model_Shifts::validate('update');
            if (!$val->run($input)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($val->errors()), 400);
            }

            // 新しい定員が現在の参加者数より少ない場合はエラー
            $capacity = \Model_Shift_Assignments::get_shift_capacity($id);
            if (!empty($input['recruit_count']) && $input['recruit_count'] < $capacity['current_participants']) {
                return $this->response(Controller_Api_Common::errorResponse('定員を現在の参加者数より少なくすることはできません', 400), 400);
            }

            $shift->set($input);
            if (!$shift->save()) {
                throw new Exception('シフトの更新に失敗しました');
            }

            $formatted_shift = Model_Shifts::find_by_id_with_assignments($shift->id);
            return $this->response(Controller_Api_Common::successResponse($formatted_shift, 'シフトを更新しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの更新に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    /**
     * シフト削除
     */
    public function action_delete($id)
    {
        try {
            $shift = Model_Shifts::find($id);
            if (!$shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            $shift->delete();
            
            return $this->response(Controller_Api_Common::successResponse(null, 'シフトを削除しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの削除に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    /**
     * シフト参加
     */
    public function action_join($id)
    {
        try {
            // シフトの存在確認
            if (!Model_Shifts::find($id)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }
            
            $user_id = Controller_Api_Common::requireCurrentUserId();
            
            if (!Model_Shift_Assignments::can_join_shift($user_id, $id)) {
                return $this->response(Controller_Api_Common::errorResponse('参加できません', 409), 409);
            }
            
            \Model_Shift_Assignments::join_shift($user_id, $id);

            return $this->response(Controller_Api_Common::successResponse(null, 'シフトに参加しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト参加に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト参加取消
     */
    public function action_cancel($id)
    {
        try {
            // シフトの存在確認
            if (!Model_Shifts::find($id)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }
            
            $user_id = Controller_Api_Common::requireCurrentUserId();
            
            if (!Model_Shift_Assignments::is_user_participating($user_id, $id)) {
                return $this->response(Controller_Api_Common::errorResponse('参加していません', 409), 409);
            }
            
            \Model_Shift_Assignments::cancel_shift($user_id, $id);

            return $this->response(Controller_Api_Common::successResponse(null, 'シフト参加を取消しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト参加取消に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
}