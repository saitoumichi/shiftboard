<?php

class Controller_Api_Shift_Assignments extends Controller_Api_Base
{
    public function action_index()
    {
        try {
            // シフト割り当て一覧を取得
            $assignments = $this->getAllAssignments();
            
            $data = array();
            foreach ($assignments as $assignment) {
                // シフト情報も含めて返す
                $shift = DB::select('*')
                    ->from('shifts')
                    ->where('id', $assignment['shift_id'])
                    ->execute()
                    ->current();
                
                if ($shift) {
                    $assignment['title'] = $shift['title'];
                    $assignment['shift_date'] = $shift['shift_date'];
                    $assignment['start_time'] = $shift['start_time'];
                    $assignment['end_time'] = $shift['end_time'];
                    $assignment['slot_count'] = $shift['slot_count'];
                    $assignment['note'] = $shift['note'];
                    
                    // 割り当て者情報を取得
                    $assigned_users = DB::select('u.name', 'sa.status', 'sa.user_id')
                        ->from('shift_assignments', 'sa')
                        ->join('users', 'INNER')
                        ->on('sa.user_id', '=', 'u.id')
                        ->where('sa.shift_id', $assignment['shift_id'])
                        ->where('sa.status', '!=', 'cancelled')
                        ->execute()
                        ->as_array();
                    
                    $assignment['assigned_users'] = $assigned_users;
                }
                
                $data[] = $assignment;
            }
            
            return $this->response(Controller_Api_Common::successResponse($data, '割り当て一覧を取得しました'));
            
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('割り当て一覧の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    public function action_show($id = null)
    {
        try {
            $validId = $this->validateId($id);
            if (!$validId) {
                return $this->response(Controller_Api_Common::errorResponse('無効なIDです', 400), 400);
            }
            
            // 特定の割り当てを取得
            $assignment = DB::select('sa.*', 's.title', 's.shift_date', 's.start_time', 's.end_time', 's.slot_count', 's.note', 'u.name as user_name')
                ->from('shift_assignments', 'sa')
                ->join('shifts', 'INNER')
                ->on('sa.shift_id', '=', 's.id')
                ->join('users', 'INNER')
                ->on('sa.user_id', '=', 'u.id')
                ->where('sa.id', $validId)
                ->execute()
                ->current();
            
            if (!$assignment) {
                return $this->response(Controller_Api_Common::errorResponse('割り当てが見つかりません', 404), 404);
            }
            
            return $this->response(Controller_Api_Common::successResponse($assignment, '割り当て詳細を取得しました'));
            
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('割り当て詳細の取得に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    public function action_create()
    {
        try {
            $input = $this->getInput();
            
            // バリデーション
            $validation = $this->validateAssignmentInput($input);
            if (!$validation['valid']) {
                return $this->response(Controller_Api_Common::errorResponse('バリデーションエラー', 400, $validation['errors']), 400);
            }
            
            // 重複チェック
            $existing = DB::select('id')
                ->from('shift_assignments')
                ->where('shift_id', $input['shift_id'])
                ->where('user_id', $input['user_id'])
                ->execute()
                ->current();
            
            if ($existing) {
                return $this->response(Controller_Api_Common::errorResponse('既にこのシフトに割り当てられています', 409), 409);
            }
            
            // シフトの定員チェック
            $shift = DB::select('slot_count')
                ->from('shifts')
                ->where('id', $input['shift_id'])
                ->execute()
                ->current();
            
            if (!$shift) {
                return $this->response(Controller_Api_Common::errorResponse('シフトが見つかりません', 404), 404);
            }
            
            $currentCount = DB::select(DB::expr('COUNT(*) as count'))
                ->from('shift_assignments')
                ->where('shift_id', $input['shift_id'])
                ->where('status', '!=', 'cancelled')
                ->execute()
                ->current()['count'];
            
            if ($currentCount >= $shift['slot_count']) {
                return $this->response(Controller_Api_Common::errorResponse('このシフトは満員です', 409), 409);
            }
            
            // 割り当てを作成
            $assignmentId = DB::insert('shift_assignments')
                ->set([
                    'shift_id' => $input['shift_id'],
                    'user_id' => $input['user_id'],
                    'status' => $input['status'] ?: 'assigned',
                    'created_at' => DB::expr('CURRENT_TIMESTAMP')
                ])
                ->execute()[0];
            
            // 作成された割り当てを取得
            $newAssignment = DB::select('sa.*', 's.title', 's.shift_date', 's.start_time', 's.end_time', 's.slot_count', 'u.name as user_name')
                ->from('shift_assignments', 'sa')
                ->join('shifts', 'INNER')
                ->on('sa.shift_id', '=', 's.id')
                ->join('users', 'INNER')
                ->on('sa.user_id', '=', 'u.id')
                ->where('sa.id', $assignmentId)
                ->execute()
                ->current();
            
            return $this->response(Controller_Api_Common::successResponse($newAssignment, '割り当てを作成しました'), 201);
            
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('割り当ての作成に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    public function action_update($id = null)
    {
        try {
            $validId = $this->validateId($id);
            if (!$validId) {
                return $this->response(Controller_Api_Common::errorResponse('無効なIDです', 400), 400);
            }
            
            $input = $this->getInput();
            
            // バリデーション
            $validation = $this->validateAssignmentUpdateInput($input);
            if (!$validation['valid']) {
                return $this->response(Controller_Api_Common::errorResponse('バリデーションエラー', 400, $validation['errors']), 400);
            }
            
            // 割り当ての存在チェック
            $assignment = DB::select('id')
                ->from('shift_assignments')
                ->where('id', $validId)
                ->execute()
                ->current();
            
            if (!$assignment) {
                return $this->response(Controller_Api_Common::errorResponse('割り当てが見つかりません', 404), 404);
            }
            
            // 更新
            $updateData = [];
            if (isset($input['status'])) {
                $updateData['status'] = $input['status'];
            }
            if (isset($input['self_word'])) {
                $updateData['self_word'] = $input['self_word'];
            }
            
            if (!empty($updateData)) {
                $updateData['updated_at'] = DB::expr('CURRENT_TIMESTAMP');
                
                DB::update('shift_assignments')
                    ->set($updateData)
                    ->where('id', $validId)
                    ->execute();
            }
            
            // 更新された割り当てを取得
            $updatedAssignment = DB::select('sa.*', 's.title', 's.shift_date', 's.start_time', 's.end_time', 's.slot_count', 'u.name as user_name')
                ->from('shift_assignments', 'sa')
                ->join('shifts', 'INNER')
                ->on('sa.shift_id', '=', 's.id')
                ->join('users', 'INNER')
                ->on('sa.user_id', '=', 'u.id')
                ->where('sa.id', $validId)
                ->execute()
                ->current();
            
            return $this->response(Controller_Api_Common::successResponse($updatedAssignment, '割り当てを更新しました'));
            
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('割り当ての更新に失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    public function action_delete($id = null)
    {
        try {
            $validId = $this->validateId($id);
            if (!$validId) {
                return $this->response(Controller_Api_Common::errorResponse('無効なIDです', 400), 400);
            }
            
            // 割り当ての存在チェック
            $assignment = DB::select('id')
                ->from('shift_assignments')
                ->where('id', $validId)
                ->execute()
                ->current();
            
            if (!$assignment) {
                return $this->response(Controller_Api_Common::errorResponse('割り当てが見つかりません', 404), 404);
            }
            
            // 論理削除（statusをcancelledに変更）
            DB::update('shift_assignments')
                ->set([
                    'status' => 'cancelled',
                    'updated_at' => DB::expr('CURRENT_TIMESTAMP')
                ])
                ->where('id', $validId)
                ->execute();
            
            return $this->response(Controller_Api_Common::successResponse(null, '割り当てをキャンセルしました'));
            
        } catch (Exception $e) {
            return $this->response(Controller_Api_Common::errorResponse('割り当てのキャンセルに失敗しました: ' . $e->getMessage(), 500), 500);
        }
    }
    
    private function getAllAssignments()
    {
        return DB::select('sa.*', 's.title', 's.shift_date', 's.start_time', 's.end_time', 's.slot_count', 'u.name as user_name')
            ->from('shift_assignments', 'sa')
            ->join('shifts', 'INNER')
            ->on('sa.shift_id', '=', 's.id')
            ->join('users', 'INNER')
            ->on('sa.user_id', '=', 'u.id')
            ->order_by('sa.created_at', 'desc')
            ->execute()
            ->as_array();
    }
    
    private function validateAssignmentInput($input)
    {
        $errors = array();
        
        if (empty($input['shift_id'])) {
            $errors[] = 'シフトIDを入力してください';
        } elseif (!is_numeric($input['shift_id'])) {
            $errors[] = 'シフトIDは数値である必要があります';
        }
        
        if (empty($input['user_id'])) {
            $errors[] = 'ユーザーIDを入力してください';
        } elseif (!is_numeric($input['user_id'])) {
            $errors[] = 'ユーザーIDは数値である必要があります';
        }
        
        if (isset($input['status']) && !in_array($input['status'], ['assigned', 'confirmed', 'cancelled'])) {
            $errors[] = 'ステータスは assigned, confirmed, cancelled のいずれかである必要があります';
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
    
    private function validateAssignmentUpdateInput($input)
    {
        $errors = array();
        
        if (isset($input['status']) && !in_array($input['status'], ['assigned', 'confirmed', 'cancelled'])) {
            $errors[] = 'ステータスは assigned, confirmed, cancelled のいずれかである必要があります';
        }
        
        if (isset($input['self_word']) && strlen($input['self_word']) > 500) {
            $errors[] = '自己PRは500文字以内で入力してください';
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
}