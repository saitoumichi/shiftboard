<?php

namespace Controller_Api; // 名前空間の追加

/**
 * API Shifts Controller
 * シフト管理用のAPIコントローラー
 */
class Controller_Shifts extends \Controller_Rest
{
    /**
     * @var array レスポンスフォーマット
     */
    protected $format = 'json';

    /**
     * 事前処理
     */
    public function before()
    {
        // 親クラスのbeforeメソッドを呼び出す
        parent::before();

        // 共通処理など、すべてのAction実行前に必要な処理をここに記述する
        // 例：認証チェックなど
    }

    /**
     * シフト一覧取得
     * GET /api/shifts
     */
    public function get_index()
    {
        try {
            // モデルからシフト一覧と参加者数を取得
            $shifts = \Model_Shifts::get_all_with_assignments();
            return $this->response(['items' => $shifts]);
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト一覧の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    /**
     * シフト詳細取得
     * GET /api/shifts/id
     */
    public function get_show($id)
    {
        try {
            $shift = \Model_Shifts::find_by_id_with_assignments($id);
            if (!$shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }
            return $this->response(Controller_Api_Common::successResponse($shift, 'シフト詳細を取得しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト作成
     * POST /api/shifts
     */
    public function post_create()
    {
        try {
            $input = \Input::post() ?: json_decode(file_get_contents('php://input'), true);

            // モデルに定義されたバリデーションルールを実行
            $val = \Model_Shifts::validate_create();
            if (!$val->run($input)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($val->errors()), 400);
            }
            
            // モデルのメソッドを呼び出してシフトを作成
            $shift = \Model_Shifts::create_shift($input);
            if (!$shift) {
                throw new Exception('シフトの作成に失敗しました');
            }

            // 作成されたシフトの詳細情報を取得して返す
            $formatted_shift = \Model_Shifts::find_by_id_with_assignments($shift->id);
            return $this->response(Controller_Api_Common::successResponse($formatted_shift, 'シフトを作成しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの作成に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    /**
     * シフト更新
     * PUT /api/shifts/id
     */
    public function put_update($id)
    {
        try {
            $input = \Input::put() ?: json_decode(file_get_contents('php://input'), true);

            // モデルに定義されたバリデーションルールを実行
            $val = \Model_Shifts::validate_update();
            if (!$val->run($input)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($val->errors()), 400);
            }

            // モデルのメソッドを呼び出してシフトを更新
            $updated_shift = \Model_Shifts::update_shift($id, $input);
            if (!$updated_shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つからないか、更新に失敗しました', 404), 404);
            }

            return $this->response(Controller_Api_Common::successResponse($updated_shift->to_array_with_assignments(), 'シフトを更新しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの更新に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * シフト削除
     * DELETE /api/shifts/id
     */
    public function delete_delete($id)
    {
        try {
            // モデルのメソッドを呼び出してシフトを削除
            if (!\Model_Shifts::delete_shift($id)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つからないか、削除に失敗しました', 404), 404);
            }
            
            return $this->response(Controller_Api_Common::successResponse(null, 'シフトを削除しました'));
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフトの削除に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
}