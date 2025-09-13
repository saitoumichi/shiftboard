<?php

/**
 * Shift Model
 * 
 * シフト管理のためのモデルクラス
 * FuelPHPのORMを使用してshiftsテーブルを操作します
 */
class Model_Shifts extends \Orm\Model
{
    /**
     * テーブル名
     */
    protected static $_table_name = 'shifts';
    
    /**
     * プライマリキー
     */
    protected static $_primary_key = array('id');
    
    /**
     * プロパティ定義
     */
    protected static $_properties = array(
        'id',
        'created_by', // DB: created_by を追加
        'shift_date',
        'start_time',
        'end_time',
        'recruit_count', // `slot_count`から変更
        'free_text',     // `note`から変更
        'created_at',
        'updated_at'
    );
    
    /**
     * データ型定義
     */
    protected static $_data_types = array(
        'id' => 'int',
        'title' => 'string',
        'shift_date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
        'slot_count' => 'int',
        'note' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    );
    
    /**
     * バリデーションルール
     */
    protected static $_validates = array(
        'shift_date' => array(
            'required' => true,
        ),
        'start_time' => array(
            'required' => true,
        ),
        'end_time' => array(
            'required' => true,
        ),
        'recruit_count' => array(
            'required' => true,
            'min' => array(1),
            'max' => array(100),
            'message' => '募集人数は1人以上100人以下で入力してください'
        )
    );
    
    /**
     * 観察者（オブザーバー）
     */
    protected static $_observers = array(
        'Orm\\Observer_CreatedAt' => array(
            'events' => array('before_insert'),
            'mysql_timestamp' => true,
            'property' => 'created_at'
        ),
        'Orm\\Observer_UpdatedAt' => array(
            'events' => array('before_update'),
            'mysql_timestamp' => true,
            'property' => 'updated_at'
        )
    );
    
    /**
     * リレーション定義
     */
    protected static $_has_many = array(
        'assignments' => array(
            'model_to' => 'Model_Shift_Assignments',
            'key_from' => 'id',
            'key_to' => 'shift_id',
            'cascade_save' => true,
            'cascade_delete' => true
        )
    );
    
    /**
     * 新規シフト作成
     * 
     * @param array $data シフトデータ
     * @return Model_Shifts|false 作成されたシフトオブジェクトまたはfalse
     */
    public static function create_shift($data)
    {
        try {
            $shift = static::forge();
            $shift->created_by = $data['created_by'];
            $shift->shift_date = $data['shift_date'];
            $shift->start_time = $data['start_time'];
            $shift->end_time = $data['end_time'];
            $shift->recruit_count = $data['recruit_count'];
            $shift->free_text = isset($data['free_text']) ? $data['free_text'] : '';
            
            if ($shift->save()) {
                return $shift;
            }
            return false;
        } catch (Exception $e) {
            \Log::error('シフト作成エラー: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 日付範囲でシフトを取得
     * 
     * @param string $start_date 開始日
     * @param string $end_date 終了日
     * @return array シフト一覧
     */
    public static function get_shifts_by_date_range($start_date, $end_date)
    {
        return static::query()
            ->where('shift_date', '>=', $start_date)
            ->where('shift_date', '<=', $end_date)
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->get();
    }
    
    /**
     * 指定した月のシフトを取得
     * 
     * @param int $year 年
     * @param int $month 月
     * @return array シフト一覧
     */
    public static function get_shifts_by_month($year, $month)
    {
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        return static::get_shifts_by_date_range($start_date, $end_date);
    }
    
    /**
     * 空きのあるシフトを取得
     * 
     * @return array 空きのあるシフト一覧
     */
    public static function get_available_shifts()
    {
        $shifts = static::query()
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->get();
        
        $available_shifts = array();
        foreach ($shifts as $shift) {
            if ($shift->has_available_slots()) {
                $available_shifts[] = $shift;
            }
        }
        
        return $available_shifts;
    }
    
    /**
     * ユーザーが参加しているシフトを取得
     * 
     * @param int $user_id ユーザーID
     * @return array 参加シフト一覧
     */
    public static function get_user_shifts($user_id)
    {
        return static::query()
            ->related('assignments')
            ->where('assignments.user_id', $user_id)
            ->where('assignments.status', '!=', 'cancelled')
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->get();
    }
    
    /**
     * シフトに空きがあるかチェック
     * 
     * @return bool 空きがある場合はtrue
     */
    public function has_available_slots()
    {
        $assigned_count = $this->get_assigned_count();
        return $assigned_count < $this->slot_count;
    }
    
    /**
     * 現在の割り当て数を取得
     * 
     * @return int 割り当て数
     */
    public function get_assigned_count()
    {
        return \Model_Shift_Assignments::query()
            ->where('shift_id', $this->id)
            ->where('status', '!=', 'cancelled')
            ->count();
    }
    
    /**
     * 空き定員数を取得
     * 
     * @return int 空き定員数
     */
    public function get_available_slots()
    {
        return $this->slot_count - $this->get_assigned_count();
    }
    
    /**
     * 割り当て済みユーザー一覧を取得
     * 
     * @return array 割り当て済みユーザー一覧
     */
    public function get_assigned_users()
    {
        return \Model_Shift_Assignments::query()
            ->related('user')
            ->where('shift_id', $this->id)
            ->where('status', '!=', 'cancelled')
            ->get();
    }
    
    /**
     * ユーザーがこのシフトに参加しているかチェック
     * 
     * @param int $user_id ユーザーID
     * @return bool 参加している場合はtrue
     */
    public function is_user_assigned($user_id)
    {
        return \Model_Shift_Assignments::query()
            ->where('shift_id', $this->id)
            ->where('user_id', $user_id)
            ->where('status', '!=', 'cancelled')
            ->count() > 0;
    }
    
    /**
     * シフトの詳細情報を配列で取得
     * 
     * @return array シフト詳細情報
     */
    public function to_array_with_assignments()
    {
        $data = $this->to_array();
        $data['assigned_count'] = $this->get_assigned_count();
        $data['available_slots'] = $this->get_available_slots();
        $data['assigned_users'] = array();
        
        $assignments = $this->get_assigned_users();
        foreach ($assignments as $assignment) {
            $data['assigned_users'][] = array(
                'id' => $assignment->user->id,
                'name' => $assignment->user->name,
                'status' => $assignment->status,
                'assigned_at' => $assignment->created_at
            );
        }
        
        return $data;
    }
    
    /**
     * シフトの統計情報を取得
     * 
     * @return array 統計情報
     */
    public function get_statistics()
    {
        return array(
            'total_slots' => $this->slot_count,
            'assigned_count' => $this->get_assigned_count(),
            'available_slots' => $this->get_available_slots(),
            'fill_rate' => $this->slot_count > 0 ? round(($this->get_assigned_count() / $this->slot_count) * 100, 1) : 0,
            'is_full' => !$this->has_available_slots(),
            'is_empty' => $this->get_assigned_count() == 0
        );
    }
    
    /**
     * シフトの期間を文字列で取得
     * 
     * @return string 期間文字列
     */
    public function get_period_string()
    {
        return sprintf('%s %s - %s', 
            $this->shift_date, 
            $this->start_time, 
            $this->end_time
        );
    }
    
    /**
     * シフトのタイトルを生成（タイトルがない場合）
     * 
     * @return string 生成されたタイトル
     */
    public function generate_title()
    {
        if (!empty($this->title)) {
            return $this->title;
        }
        
        $date = new DateTime($this->shift_date);
        return sprintf('%sのシフト (%s〜%s)', 
            $date->format('n月j日'),
            $this->start_time,
            $this->end_time
        );
    }
    // `Model_Shifts` クラス内に以下のメソッドを追加・修正
    
    /**
     * シフト一覧と参加者数を取得
     * ORMを使用してJOIN操作を行う
     */
    public static function get_all_with_assignments()
    {
        $rows = static::query()
            ->select('t0.*', \DB::expr("COUNT(CASE WHEN t1.status != 'cancelled' THEN t1.user_id END) AS joined_count"))
            ->related('assignments', [
                'where' => [
                    ['assignments.status', '!=', 'cancelled']
                ],
                'join_type' => 'left'
            ])
            ->group_by('t0.id')
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->get();
            
        return array_map(function($row) {
            $data = $row->to_array();
            $data['joined_count'] = $data['joined_count'] ?? 0;
            return $data;
        }, $rows);
    }
    
    /**
     * シフト詳細と参加者情報を取得
     */
    public static function find_by_id_with_assignments($id)
    {
        $shift = static::find($id, [
            'related' => ['assignments']
        ]);
        
        if ($shift) {
            $shift_data = $shift->to_array();
            $assigned_users = [];
            foreach ($shift->assignments as $assignment) {
                if ($assignment->status !== 'cancelled') {
                    // ここでユーザー情報を取得するために `Model_Shift_Assignments` のリレーションを利用する
                    $user = \Model_Users::find($assignment->user_id);
                    $assigned_users[] = [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'status' => $assignment->status,
                        'self_word' => $assignment->self_word
                    ];
                }
            }
            $shift_data['assigned_users'] = $assigned_users;
            return $shift_data;
        }
        return null;
    }
}
