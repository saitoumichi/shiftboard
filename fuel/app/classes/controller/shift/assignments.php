<?php

use Fuel\Core\Session;
use Fuel\Core\DB;

class Controller_Shift_Assignment extends \Fuel\Core\Controller
{
    // 自分の割り当て一覧ページ
    public function action_my_assignments()
    {
        // 自分の割り当て一覧ページ
        $user_id = Session::get('user_id'); // 認証ユーザーのIDを使用
        if (!$user_id) {
            Session::set_flash('error', 'ログインしてください');
            return \Fuel\Core\Response::redirect('users/login');
        }
        
        // JOINクエリで一括取得してN+1問題を解決
        $query = DB::select(
            'sa.id as assignment_id',
            'sa.shift_id',
            'sa.user_id',
            'sa.status as assignment_status',
            'sa.self_word',
            'sa.created_at as assignment_created_at',
            's.id as shift_id',
            's.shift_date',
            's.start_time',
            's.end_time',
            's.recruit_count',
            's.free_text',
            's.created_by',
            's.created_at as shift_created_at',
            's.updated_at as shift_updated_at',
            DB::expr('COUNT(sa2.id) as joined_count')
        )
        ->from(['shift_assignments', 'sa'])
        ->join(['shifts', 's'], 'INNER')
            ->on('sa.shift_id', '=', 's.id')
        ->join(['shift_assignments', 'sa2'], 'LEFT')
            ->on('s.id', '=', 'sa2.shift_id')
            ->on('sa2.status', '!=', DB::expr("'cancelled'"))
        ->where('sa.user_id', $user_id)
        ->where('sa.status', 'assigned')
        ->group_by('sa.id', 's.id')
        ->order_by('sa.id', 'desc');
        $assignments_data = $query->execute();
        
        // データを整形
        $assignments_with_status = array();
        foreach ($assignments_data as $row) {
            $joined_count = (int)$row['joined_count'];
            $recruit_count = (int)$row['recruit_count'];
            $remaining = max(0, $recruit_count - $joined_count);
            $is_full = ($joined_count >= $recruit_count);
            
            $assignments_with_status[] = array(
                'assignment' => (object)array(
                    'id' => $row['assignment_id'],
                    'shift_id' => $row['shift_id'],
                    'user_id' => $row['user_id'],
                    'status' => $row['assignment_status'],
                    'self_word' => $row['self_word'],
                    'created_at' => $row['assignment_created_at']
                ),
                'shift' => (object)array(
                    'id' => $row['shift_id'],
                    'shift_date' => $row['shift_date'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'recruit_count' => $row['recruit_count'],
                    'free_text' => $row['free_text'],
                    'created_by' => $row['created_by'],
                    'created_at' => $row['shift_created_at'],
                    'updated_at' => $row['shift_updated_at']
                ),
                'joined_count' => $joined_count,
                'recruit_count' => $recruit_count,
                'remaining' => $remaining,
                'is_full' => $is_full,
                'status_text' => $is_full ? '満員' : ($remaining . '名募集中'),
            );
        }
        
        $data = array();
        $data['current_user_id'] = $user_id;
        $data['assignments_with_status'] = $assignments_with_status;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/my_assignments', $data));
    }

    // シフト割り当て管理ページのメインアクション
    public function action_assignments()
    {
        $data = array();
        
        // セッションからユーザー情報を取得
        $user_id = Session::get('user_id'); // 認証ユーザーのIDを使用
        if (!$user_id) {
            Session::set_flash('error', 'ログインしてください');
            return \Fuel\Core\Response::redirect('users/login');
        }
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/index', $data));
    }
    
    // 特定のシフトの割り当て管理ページ
    public function action_manage($shift_id = null)
    {
        if (!$shift_id) {
            return \Fuel\Core\Response::redirect('/shift/assignments');
        }
        
        $data = array();
        $data['shift_id'] = $shift_id;
        
        // セッションからユーザー情報を取得
        $user_id = Session::get('user_id'); // 認証ユーザーのIDを使用
        if (!$user_id) {
            Session::set_flash('error', 'ログインしてください');
            return \Fuel\Core\Response::redirect('users/login');
        }
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/manage', $data));
    }
    
    // 割り当てカレンダー表示ページ
    public function action_calendar()
    {
        $data = array();
        
        // セッションからユーザー情報を取得
        $user_id = Session::get('user_id'); // 認証ユーザーのIDを使用
        if (!$user_id) {
            Session::set_flash('error', 'ログインしてください');
            return \Fuel\Core\Response::redirect('users/login');
        }
        $data['current_user_id'] = $user_id;
        
        // ビューをレンダリング
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/calendar', $data));
    }
    
    /**
     * 特定のシフトIDに関連するすべてのshift_assignmentsレコードを取得
     * * @param int $shift_id シフトID
     * @return array 割り当てレコードの配列
     */
    public function get_assignments_by_shift_id($shift_id)
    {
        if (!$shift_id || !is_numeric($shift_id)) {
            return array();
        }
        
        // 特定のシフトIDに関連するすべての割り当てを取得
        $assignments = \Model_Shift_Assignment::query()
            ->where('shift_id', $shift_id)
            ->related('user')  // ユーザー情報も一緒に取得
            ->order_by('created_at', 'asc')  // 登録順でソート
            ->get();
            
        return $assignments;
    }
    
    /**
     * 特定のシフトIDに関連する割り当ての統計情報を取得
     * * @param int $shift_id シフトID
     * @return array 統計情報の配列
     */
    public function get_shift_assignment_stats($shift_id)
    {
        if (!$shift_id || !is_numeric($shift_id)) {
            return array(
                'total_assignments' => 0,
                'assigned_count' => 0,
                'confirmed_count' => 0,
                'cancelled_count' => 0,
                'active_assignments' => 0
            );
        }
        
        // 全割り当てを取得
        $all_assignments = \Model_Shift_Assignment::query()
            ->where('shift_id', $shift_id)
            ->get();
            
        // 統計を計算
        $stats = array(
            'total_assignments' => count($all_assignments),
            'assigned_count' => 0,
            'confirmed_count' => 0,
            'cancelled_count' => 0,
            'active_assignments' => 0
        );
        
        foreach ($all_assignments as $assignment) {
            switch ($assignment->status) {
                case 'assigned':
                    $stats['assigned_count']++;
                    $stats['active_assignments']++;
                    break;
                case 'confirmed':
                    $stats['confirmed_count']++;
                    $stats['active_assignments']++;
                    break;
                case 'cancelled':
                    $stats['cancelled_count']++;
                    break;
            }
        }
        
        return $stats;
    }
    
    /**
     * 特定のシフトIDの参加者一覧を表示するページ
     * * @param int $shift_id シフトID
     * @return Response ビューレスポンス
     */
    public function action_participants($shift_id = null)
    {
        if (!$shift_id || !is_numeric($shift_id)) {
            Session::set_flash('error', '無効なシフトIDです');
            return \Fuel\Core\Response::redirect('shifts');
        }
        
        // シフトの存在確認
        $shift = \Model_Shift::find($shift_id);
        if (!$shift) {
            Session::set_flash('error', 'シフトが見つかりません');
            return \Fuel\Core\Response::redirect('shifts');
        }
        
        // 割り当てを取得
        $assignments = $this->get_assignments_by_shift_id($shift_id);
        $stats = $this->get_shift_assignment_stats($shift_id);
        
        $data = array(
            'shift' => $shift,
            'assignments' => $assignments,
            'stats' => $stats
        );
        
        return \Fuel\Core\Response::forge(\Fuel\Core\View::forge('shift_assignments/participants', $data));
    }
    
    /**
     * 特定のシフトIDに関連する割り当てをJSON形式で返すAPIエンドポイント
     * * @param int $shift_id シフトID
     * @return Response JSONレスポンス
     */
    public function action_get_assignments($shift_id = null)
    {
        if (!$shift_id || !is_numeric($shift_id)) {
            return \Fuel\Core\Response::forge(json_encode(array(
                'success' => false,
                'error' => 'Invalid shift ID'
            )), 400)->set_header('Content-Type', 'application/json');
        }
        
        // シフトの存在確認
        $shift = \Model_Shift::find($shift_id);
        if (!$shift) {
            return \Fuel\Core\Response::forge(json_encode(array(
                'success' => false,
                'error' => 'Shift not found'
            )), 404)->set_header('Content-Type', 'application/json');
        }
        
        // 割り当てを取得
        $assignments = $this->get_assignments_by_shift_id($shift_id);
        $stats = $this->get_shift_assignment_stats($shift_id);
        
        // レスポンス用にデータを整形
        $assignments_data = array();
        foreach ($assignments as $assignment) {
            $assignments_data[] = array(
                'id' => (int)$assignment->id,
                'shift_id' => (int)$assignment->shift_id,
                'user_id' => (int)$assignment->user_id,
                'user_name' => $assignment->user ? $assignment->user->name : 'Unknown User',
                'user_color' => $assignment->user ? $assignment->user->color : '#000000',
                'status' => $assignment->status,
                'self_word' => $assignment->self_word,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->updated_at
            );
        }
        
        return \Fuel\Core\Response::forge(json_encode(array(
            'success' => true,
            'data' => array(
                'shift_id' => (int)$shift_id,
                'assignments' => $assignments_data,
                'stats' => $stats
            )
        )))->set_header('Content-Type', 'application/json');
    }
}