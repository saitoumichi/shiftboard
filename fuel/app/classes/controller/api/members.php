<?php

/**
 * メンバー管理用のAPIコントローラー
 */
class Controller_Api_Members extends Controller
{
    /**
     * メンバー一覧取得 / メンバー作成
     */
    public function action_index()
    {
        // POSTリクエストの場合はメンバー作成
        if (Input::method() === 'POST') {
            return $this->action_create();
        }
        
        try {
            // メンバー一覧を取得（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            $stmt = $pdo->query("SELECT * FROM members ORDER BY name ASC");
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = array();
            foreach ($members as $member) {
                // 各メンバーの参加シフト数を取得
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as shift_count 
                    FROM shift_assignments sa 
                    WHERE sa.member_id = ? AND sa.status != 'cancelled'
                ");
                $stmt->execute([$member['id']]);
                $shift_count = $stmt->fetch(PDO::FETCH_ASSOC)['shift_count'];
                
                $data[] = array(
                    'id' => $member['id'],
                    'name' => $member['name'],
                    'role' => $member['role'],
                    'color' => $member['color'],
                    'is_active' => $member['is_active'],
                    'shift_count' => $shift_count,
                    'created_at' => $member['created_at'],
                    'updated_at' => $member['updated_at']
                );
            }

            return $this->response(array(
                'success' => true,
                'data' => $data,
                'message' => 'メンバー一覧を取得しました'
            ));
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'メンバー一覧の取得に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }
    
    /**
     * メンバー作成
     */
    public function action_create()
    {
        try {
            $name = Input::post('name');
            $role = Input::post('role', 'member');
            $color = Input::post('color', '#3498db');
            $is_active = Input::post('is_active', 1);
            
            // バリデーション
            if (!$name) {
                return $this->response(array(
                    'success' => false,
                    'message' => '名前は必須です'
                ), 400);
            }
            
            if (!in_array($role, ['member', 'admin'])) {
                return $this->response(array(
                    'success' => false,
                    'message' => '権限はmemberまたはadminである必要があります'
                ), 400);
            }
            
            // メンバー作成（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            $stmt = $pdo->prepare("
                INSERT INTO members (name, role, color, is_active) 
                VALUES (?, ?, ?, ?)
            ");
            $result = $stmt->execute([$name, $role, $color, $is_active]);
            
            if ($result) {
                $member_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
                $stmt->execute([$member_id]);
                $member = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $data = array(
                    'id' => $member['id'],
                    'name' => $member['name'],
                    'role' => $member['role'],
                    'color' => $member['color'],
                    'is_active' => $member['is_active'],
                    'shift_count' => 0,
                    'created_at' => $member['created_at'],
                    'updated_at' => $member['updated_at']
                );
                
                return $this->response(array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'メンバーを作成しました'
                ));
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーの作成に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'メンバーの作成に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }
    
    /**
     * メンバー詳細取得
     */
    public function action_show($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーIDが指定されていません'
                ), 400);
            }
            
            // メンバー詳細を取得（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーが見つかりません'
                ), 404);
            }
            
            // 参加シフト一覧を取得
            $stmt = $pdo->prepare("
                SELECT s.*, sa.status, sa.created_at as joined_at
                FROM shift_assignments sa
                JOIN shifts s ON sa.shift_id = s.id
                WHERE sa.member_id = ? AND sa.status != 'cancelled'
                ORDER BY s.shift_date ASC, s.start_time ASC
            ");
            $stmt->execute([$id]);
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = array(
                'id' => $member['id'],
                'name' => $member['name'],
                'role' => $member['role'],
                'color' => $member['color'],
                'is_active' => $member['is_active'],
                'shift_count' => count($shifts),
                'shifts' => $shifts,
                'created_at' => $member['created_at'],
                'updated_at' => $member['updated_at']
            );
            
            return $this->response(array(
                'success' => true,
                'data' => $data,
                'message' => 'メンバー詳細を取得しました'
            ));
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'メンバー詳細の取得に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }
    
    /**
     * メンバー更新
     */
    public function action_update($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーIDが指定されていません'
                ), 400);
            }
            
            $name = Input::post('name');
            $role = Input::post('role');
            $color = Input::post('color');
            $is_active = Input::post('is_active');
            
            // バリデーション
            if (!$name) {
                return $this->response(array(
                    'success' => false,
                    'message' => '名前は必須です'
                ), 400);
            }
            
            if ($role && !in_array($role, ['member', 'admin'])) {
                return $this->response(array(
                    'success' => false,
                    'message' => '権限はmemberまたはadminである必要があります'
                ), 400);
            }
            
            // メンバー更新（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            
            // メンバーの存在確認
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $existing_member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_member) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーが見つかりません'
                ), 404);
            }
            
            // 更新フィールドを構築
            $update_fields = array();
            $update_values = array();
            
            if ($name !== null) {
                $update_fields[] = "name = ?";
                $update_values[] = $name;
            }
            if ($role !== null) {
                $update_fields[] = "role = ?";
                $update_values[] = $role;
            }
            if ($color !== null) {
                $update_fields[] = "color = ?";
                $update_values[] = $color;
            }
            if ($is_active !== null) {
                $update_fields[] = "is_active = ?";
                $update_values[] = $is_active;
            }
            
            if (empty($update_fields)) {
                return $this->response(array(
                    'success' => false,
                    'message' => '更新する項目がありません'
                ), 400);
            }
            
            $update_values[] = $id;
            $sql = "UPDATE members SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($update_values);
            
            if ($result) {
                // 更新されたメンバーを取得
                $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
                $stmt->execute([$id]);
                $member = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // 参加シフト数を取得
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as shift_count 
                    FROM shift_assignments sa 
                    WHERE sa.member_id = ? AND sa.status != 'cancelled'
                ");
                $stmt->execute([$id]);
                $shift_count = $stmt->fetch(PDO::FETCH_ASSOC)['shift_count'];
                
                $data = array(
                    'id' => $member['id'],
                    'name' => $member['name'],
                    'role' => $member['role'],
                    'color' => $member['color'],
                    'is_active' => $member['is_active'],
                    'shift_count' => $shift_count,
                    'created_at' => $member['created_at'],
                    'updated_at' => $member['updated_at']
                );
                
                return $this->response(array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'メンバーを更新しました'
                ));
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーの更新に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'メンバーの更新に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }
    
    /**
     * メンバー削除
     */
    public function action_delete($id = null)
    {
        try {
            if (!$id) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーIDが指定されていません'
                ), 400);
            }
            
            // メンバー削除（PDO直接接続）
            $pdo = new PDO('mysql:host=127.0.0.1;port=13306;dbname=shiftboard', 'app', 'app_pass');
            
            // メンバーの存在確認
            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーが見つかりません'
                ), 404);
            }
            
            // 参加シフトがあるかチェック
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM shift_assignments 
                WHERE member_id = ? AND status != 'cancelled'
            ");
            $stmt->execute([$id]);
            $assigned_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($assigned_count > 0) {
                return $this->response(array(
                    'success' => false,
                    'message' => '参加シフトがあるメンバーは削除できません。先にシフトの参加を取り消してください。'
                ), 400);
            }
            
            // メンバー削除（CASCADEでshift_assignmentsも削除される）
            $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return $this->response(array(
                    'success' => true,
                    'message' => 'メンバーを削除しました'
                ));
            } else {
                return $this->response(array(
                    'success' => false,
                    'message' => 'メンバーの削除に失敗しました'
                ), 500);
            }
            
        } catch (Exception $e) {
            return $this->response(array(
                'success' => false,
                'message' => 'メンバーの削除に失敗しました: ' . $e->getMessage()
            ), 500);
        }
    }

    /**
     * JSONレスポンスを返す
     */
    private function response($data, $status = 200)
    {
        $response = Response::forge(json_encode($data), $status);
        $response->set_header('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }
}
