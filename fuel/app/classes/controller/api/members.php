<?php

/**
 * API Members Controller
 * 
 * メンバー管理用のAPIコントローラー
 */
class Controller_Api_Members extends \Fuel\Core\Controller
{
    /**
     * メンバー一覧取得
     */
    public function action_index()
    {
        try {
            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // メンバー一覧を取得
            $stmt = $pdo->prepare("SELECT * FROM members ORDER BY name ASC");
            $stmt->execute();
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // メンバーデータをフォーマット
            $data = array();
            foreach ($members as $member) {
                $data[] = array(
                    'id' => (int)$member['id'],
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'role' => $member['role'],
                    'created_at' => $member['created_at'],
                    'updated_at' => $member['updated_at']
                );
            }

            return $this->response(Controller_Api_Common::successResponse($data, 'メンバー一覧を取得しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('メンバー一覧の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * メンバー作成
     */
    public function action_create()
    {
        try {
            // 入力データを取得
            $name = \Fuel\Core\Input::post('name');
            $email = \Fuel\Core\Input::post('email');
            $role = \Fuel\Core\Input::post('role', 'member');

            // バリデーション
            $errors = array();
            if (empty($name)) {
                $errors[] = '名前は必須です';
            }
            if (empty($email)) {
                $errors[] = 'メールアドレスは必須です';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = '有効なメールアドレスを入力してください';
            }

            if (!empty($errors)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($errors), 400);
            }

            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // メールアドレスの重複チェック
            $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return $this->response(Controller_Api_Common::errorResponse('このメールアドレスは既に登録されています', 409), 409);
            }

            // メンバーを作成
            $stmt = $pdo->prepare("
                INSERT INTO members (name, email, role, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $role]);

            $member_id = $pdo->lastInsertId();

            // 作成されたメンバーの情報を取得
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($member) {
                $formatted_member = array(
                    'id' => (int)$member['id'],
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'role' => $member['role'],
                    'created_at' => $member['created_at'],
                    'updated_at' => $member['updated_at']
                );
                
                return $this->response(Controller_Api_Common::successResponse($formatted_member, 'メンバーを作成しました'));
            } else {
                return $this->response(Controller_Api_Common::errorResponse('メンバーの作成に失敗しました', 500), 500);
            }

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('メンバーの作成に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * メンバー詳細取得
     */
    public function action_show($id)
    {
        try {
            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // メンバー詳細を取得
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$member) {
                return $this->response(Controller_Api_Common::errorResponse('メンバーが見つかりません', 404), 404);
            }

            $formatted_member = array(
                'id' => (int)$member['id'],
                'name' => $member['name'],
                'email' => $member['email'],
                'role' => $member['role'],
                'created_at' => $member['created_at'],
                'updated_at' => $member['updated_at']
            );

            return $this->response(Controller_Api_Common::successResponse($formatted_member, 'メンバー詳細を取得しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('メンバー詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * メンバー更新
     */
    public function action_update($id)
    {
        try {
            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // メンバーの存在確認
            $stmt = $pdo->prepare("SELECT id FROM members WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return $this->response(Controller_Api_Common::errorResponse('メンバーが見つかりません', 404), 404);
            }

            // 入力データを取得
            $name = \Fuel\Core\Input::post('name');
            $email = \Fuel\Core\Input::post('email');
            $role = \Fuel\Core\Input::post('role');

            // バリデーション
            $errors = array();
            if (empty($name)) {
                $errors[] = '名前は必須です';
            }
            if (empty($email)) {
                $errors[] = 'メールアドレスは必須です';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = '有効なメールアドレスを入力してください';
            }

            if (!empty($errors)) {
                return $this->response(Controller_Api_Common::validationErrorResponse($errors), 400);
            }

            // メールアドレスの重複チェック（自分以外）
            $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                return $this->response(Controller_Api_Common::errorResponse('このメールアドレスは既に登録されています', 409), 409);
            }

            // メンバーを更新
            $stmt = $pdo->prepare("
                UPDATE members 
                SET name = ?, email = ?, role = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $role, $id]);

            // 更新されたメンバーの情報を取得
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($member) {
                $formatted_member = array(
                    'id' => (int)$member['id'],
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'role' => $member['role'],
                    'created_at' => $member['created_at'],
                    'updated_at' => $member['updated_at']
                );
                
                return $this->response(Controller_Api_Common::successResponse($formatted_member, 'メンバーを更新しました'));
            } else {
                return $this->response(Controller_Api_Common::errorResponse('メンバーの更新に失敗しました', 500), 500);
            }

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('メンバーの更新に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }

    /**
     * メンバー削除
     */
    public function action_delete($id)
    {
        try {
            // データベース接続を取得
            $pdo = Controller_Api_Common::getDbConnection();

            // メンバーの存在確認
            $stmt = $pdo->prepare("SELECT id FROM members WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return $this->response(Controller_Api_Common::errorResponse('メンバーが見つかりません', 404), 404);
            }

            // 関連するシフト参加情報を削除
            $stmt = $pdo->prepare("DELETE FROM shift_assignments WHERE member_id = ?");
            $stmt->execute([$id]);

            // メンバーを削除
            $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
            $stmt->execute([$id]);

            return $this->response(Controller_Api_Common::successResponse(null, 'メンバーを削除しました'));

        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('メンバーの削除に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
}