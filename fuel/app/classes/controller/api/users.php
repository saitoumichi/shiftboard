<?php

/**
 * API Users Controller
 * 
 * ユーザー管理用のAPIコントローラー
 */
class Controller_Api_Users extends \Fuel\Core\Controller
{
    /**
     * レスポンスを返す
     * 
     * @param mixed $data レスポンスデータ
     * @param int $status HTTPステータスコード
     * @return \Fuel\Core\Response
     */
    protected function response($data, $status = 200)
    {
        $response = new \Fuel\Core\Response();
        $response->set_status($status);
        $response->set_header('Content-Type', 'application/json; charset=utf-8');
        $response->body = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        return $response;
    }

    /**
     * ユーザー一覧取得
     */
    public function action_index()
    {
        try {
            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // ユーザー一覧を取得
            $stmt = $pdo->prepare("
                SELECT id, name, role, color, is_active, created_at, updated_at
                FROM users
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = array();
            foreach ($users as $user) {
                // 参加中のシフト数を取得
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as shift_count
                    FROM shift_assignments sa
                    WHERE sa.member_id = ? AND sa.status = 'confirmed'
                ");
                $stmt->execute([$user['id']]);
                $shift_count = $stmt->fetch(PDO::FETCH_ASSOC)['shift_count'];
                
                $user['shift_count'] = $shift_count;
                $data[] = $user;
            }

            return $this->response(Controller_Api_Common::successResponse($data, 'ユーザー一覧を取得しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('ユーザー一覧の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * ユーザー詳細取得
     */
    public function action_show($id)
    {
        try {
            if (!$id) {
                return $this->response(Controller_Api_Common::errorResponse('ユーザーIDが指定されていません', 400), 400);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // ユーザー詳細を取得
            $stmt = $pdo->prepare("
                SELECT id, name, role, color, is_active, created_at, updated_at
                FROM users
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->response(Controller_Api_Common::errorResponse('ユーザーが見つかりません', 404), 404);
            }

            // 参加中のシフト一覧を取得
            $stmt = $pdo->prepare("
                SELECT s.id, s.title, s.shift_date, s.start_time, s.end_time, s.note, sa.status, sa.created_at as joined_at
                FROM shifts s
                INNER JOIN shift_assignments sa ON s.id = sa.shift_id
                WHERE sa.member_id = ? AND sa.status != 'cancelled'
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$id]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $user['assignments'] = $assignments;

            return $this->response(Controller_Api_Common::successResponse($user, 'ユーザー詳細を取得しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('ユーザー詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * ユーザー作成
     */
    public function action_create()
    {
        try {
            // JSONデータを取得
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                return $this->response(Controller_Api_Common::errorResponse('無効なJSONデータです', 400), 400);
            }

            // バリデーション
            $name = trim($input['name'] ?? '');
            $role = trim($input['role'] ?? 'member');
            $color = trim($input['color'] ?? '#4ECDC4');
            $is_active = isset($input['is_active']) ? (bool)$input['is_active'] : true;

            if (empty($name)) {
                return $this->response(Controller_Api_Common::errorResponse('名前は必須です', 400), 400);
            }

            if (!in_array($role, ['member', 'admin'])) {
                return $this->response(Controller_Api_Common::errorResponse('無効なロールです', 400), 400);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // ユーザーを作成
            $stmt = $pdo->prepare("
                INSERT INTO users (name, role, color, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$name, $role, $color, $is_active]);
            
            $user_id = $pdo->lastInsertId();

            // 作成されたユーザー情報を取得
            $stmt = $pdo->prepare("
                SELECT id, name, role, color, is_active, created_at, updated_at
                FROM users
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(Controller_Api_Common::successResponse($user, 'ユーザーを作成しました'), 201);

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('ユーザーの作成に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * ユーザー更新
     */
    public function action_update($id)
    {
        try {
            if (!$id) {
                return $this->response(Controller_Api_Common::errorResponse('ユーザーIDが指定されていません', 400), 400);
            }

            // JSONデータを取得
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                return $this->response(Controller_Api_Common::errorResponse('無効なJSONデータです', 400), 400);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // ユーザーの存在確認
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return $this->response(Controller_Api_Common::errorResponse('ユーザーが見つかりません', 404), 404);
            }

            // 更新フィールドを構築
            $update_fields = array();
            $params = array();

            if (isset($input['name'])) {
                $name = trim($input['name']);
                if (empty($name)) {
                    return $this->response(Controller_Api_Common::errorResponse('名前は必須です', 400), 400);
                }
                $update_fields[] = 'name = ?';
                $params[] = $name;
            }

            if (isset($input['role'])) {
                $role = trim($input['role']);
                if (!in_array($role, ['member', 'admin'])) {
                    return $this->response(Controller_Api_Common::errorResponse('無効なロールです', 400), 400);
                }
                $update_fields[] = 'role = ?';
                $params[] = $role;
            }

            if (isset($input['color'])) {
                $update_fields[] = 'color = ?';
                $params[] = trim($input['color']);
            }

            if (isset($input['is_active'])) {
                $update_fields[] = 'is_active = ?';
                $params[] = (bool)$input['is_active'];
            }

            if (empty($update_fields)) {
                return $this->response(Controller_Api_Common::errorResponse('更新するフィールドが指定されていません', 400), 400);
            }

            $update_fields[] = 'updated_at = NOW()';
            $params[] = $id;

            // ユーザーを更新
            $stmt = $pdo->prepare("
                UPDATE users 
                SET " . implode(', ', $update_fields) . "
                WHERE id = ?
            ");
            $stmt->execute($params);

            // 更新されたユーザー情報を取得
            $stmt = $pdo->prepare("
                SELECT id, name, role, color, is_active, created_at, updated_at
                FROM users
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->response(Controller_Api_Common::successResponse($user, 'ユーザーを更新しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('ユーザーの更新に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * ユーザー削除
     */
    public function action_delete($id)
    {
        try {
            if (!$id) {
                return $this->response(Controller_Api_Common::errorResponse('ユーザーIDが指定されていません', 400), 400);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // ユーザーの存在確認
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return $this->response(Controller_Api_Common::errorResponse('ユーザーが見つかりません', 404), 404);
            }

            // 参加中のシフトがあるかチェック
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM shift_assignments
                WHERE member_id = ? AND status = 'confirmed'
            ");
            $stmt->execute([$id]);
            $assignment_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($assignment_count > 0) {
                return $this->response(Controller_Api_Common::errorResponse('割り当て中のシフトがあるため削除できません', 409), 409);
            }

            // ユーザーを削除（割り当て履歴は残す）
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            return $this->response(Controller_Api_Common::successResponse(null, 'ユーザーを削除しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('ユーザーの削除に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
}
