<?php

/**
 * Shift Model
 * * シフト管理のためのモデルクラス
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
        'created_by',
        'shift_date',
        'start_time',
        'end_time',
        'recruit_count',
        'free_text',
        'created_at',
        'updated_at'
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
        ),
        'Orm\\Observer_Validation' => array(
            'events' => array('before_save')
        )
    );
    
    /**
     * バリデーションルールを定義
     */
    public static function validate($factory)
    {
        $val = \Validation::forge($factory);
        $val->add_field('created_by', '作成者ID', 'required|numeric_min[1]');
        $val->add_field('shift_date', 'シフト日付', 'required|valid_date[Y-m-d]');
        $val->add_field('start_time', '開始時刻', 'required|valid_time');
        $val->add_field('end_time', '終了時刻', 'required|valid_time');
        $val->add_field('recruit_count', '募集人数', 'required|numeric_min[1]');
        $val->add_field('free_text', '備考', 'max_length[255]');
        
        // 終了時刻が開始時刻より後であることのカスタムバリデーション
        $val->add_callable(new \My_Validation_Rules());
        $val->add('end_time')->add_rule('is_later_than_start_time', 'start_time');
        
        return $val;
    }
    
    /**
     * リレーション定義
     */
    protected static $_has_many = array(
        'assignments' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Shift_Assignments',
            'key_to' => 'shift_id',
            'cascade_save' => true,
            'cascade_delete' => true,
        ),
    );

    /**
     * シフト一覧を参加者数付きで取得する
     * @return array
     */
    public static function get_all_with_assignments()
    {
        $query = static::query();
        $rows = $query
            ->select('t0.*', \DB::expr("COUNT(CASE WHEN t1.status != 'cancelled' THEN t1.user_id END) AS joined_count"))
            ->related('assignments', array('join_type' => 'left'))
            ->group_by('t0.id')
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->get();
        
        $result = [];
        foreach ($rows as $row) {
            $data = $row->to_array();
            $data['joined_count'] = $data['joined_count'] ?? 0;
            $result[] = $data;
        }

        return $result;
    }

    /**
     * IDを指定してシフト詳細と参加者情報を取得する
     * @param int $id
     * @return array|null
     */
    public static function find_by_id_with_assignments($id)
    {
        $shift = static::find($id);

        if (!$shift) {
            return null;
        }

        $shift_data = $shift->to_array();

        // 参加者情報を取得
        $assignments = \Model_Shift_Assignments::query()
            ->related('user')
            ->where('shift_id', $id)
            ->where('status', '!=', 'cancelled')
            ->get();

        $assigned_users = [];
        foreach ($assignments as $assignment) {
            $assigned_users[] = [
                'user_id' => $assignment->user->id,
                'name' => $assignment->user->name,
                'status' => $assignment->status,
                'self_word' => $assignment->self_word,
            ];
        }

        $shift_data['assigned_users'] = $assigned_users;
        return $shift_data;
    }
}