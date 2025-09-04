<?php

/**
 * API User Shifts Controller
 * 
 * ユーザーのシフト管理用のAPIコントローラー
 */
class Controller_Api_User_Shifts extends \Fuel\Core\Controller
{
    /**
     * ユーザーのシフト一覧取得
     */
    public function action_index()
    {
        try {
            // 認証済みユーザーIDを取得
            try {
                $user_id = Controller_Api_Common::requireCurrentUserId();
            } catch (Exception $e) {
                return $this->response(Controller_Api_Common::errorResponse($e->getMessage(), 401), 401);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // ユーザーのシフト一覧を取得
            $stmt = $pdo->prepare("
                SELECT s.*, sa.created_at as joined_at
                FROM shifts s
                INNER JOIN shift_assignments sa ON s.id = sa.shift_id
                WHERE sa.member_id = ? AND sa.status != 'cancelled'
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$user_id]);
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = array();
            foreach ($shifts as $shift) {
                // 参加者情報を取得
                $stmt = $pdo->prepare("
                    SELECT m.name, sa.status 
                    FROM shift_assignments sa 
                    JOIN members m ON sa.member_id = m.id 
                    WHERE sa.shift_id = ? AND sa.status != 'cancelled'
                ");
                $stmt->execute([$shift['id']]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $assigned_users = array();
                foreach ($assignments as $assignment) {
                    $assigned_users[] = array(
                        'name' => $assignment['name'],
                        'status' => $assignment['status']
                    );
                }

                // シフトデータに参加者情報を追加
                $shift['assigned_users'] = $assigned_users;
                $shift['joined_at'] = $shift['joined_at'];

                // 共通関数でフォーマット
                $data[] = Controller_Api_Common::formatShiftData($shift);
            }

            return $this->response(Controller_Api_Common::successResponse($data, '自分のシフト一覧を取得しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト一覧の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * ユーザーのシフト詳細取得
     */
    public function action_show($id)
    {
        try {
            // 認証済みユーザーIDを取得
            try {
                $user_id = Controller_Api_Common::requireCurrentUserId();
            } catch (Exception $e) {
                return $this->response(Controller_Api_Common::errorResponse($e->getMessage(), 401), 401);
            }

            // シフトの存在確認
            if (!Controller_Api_Common::shiftExists($id)) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            // ユーザーが参加しているかチェック
            if (!Controller_Api_Common::isUserParticipating($user_id, $id)) {
                return $this->response(Controller_Api_Common::errorResponse('このシフトに参加していません', 403), 403);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // シフト詳細を取得
            $stmt = $pdo->prepare("
                SELECT s.*, sa.created_at as joined_at
                FROM shifts s
                INNER JOIN shift_assignments sa ON s.id = sa.shift_id
                WHERE s.id = ? AND sa.member_id = ? AND sa.status != 'cancelled'
            ");
            $stmt->execute([$id, $user_id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }

            // 参加者情報を取得
            $stmt = $pdo->prepare("
                SELECT m.name, sa.status 
                FROM shift_assignments sa 
                JOIN members m ON sa.member_id = m.id 
                WHERE sa.shift_id = ? AND sa.status != 'cancelled'
            ");
            $stmt->execute([$id]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $assigned_users = array();
            foreach ($assignments as $assignment) {
                $assigned_users[] = array(
                    'name' => $assignment['name'],
                    'status' => $assignment['status']
                );
            }

            // シフトデータに参加者情報を追加
            $shift['assigned_users'] = $assigned_users;
            $shift['joined_at'] = $shift['joined_at'];

            // 共通関数でフォーマット
            $formatted_shift = Controller_Api_Common::formatShiftData($shift);

            return $this->response(Controller_Api_Common::successResponse($formatted_shift, 'シフト詳細を取得しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('シフト詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
}
