<?php

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
     * 新規シフト作成用バリデーションルールを定義
     */
    public static function validate_create($factory = 'create')
    {
        $val = \Validation::forge($factory);
        $val->add_field('created_by', '作成者ID', 'required|numeric_min[1]');
        $val->add_field('shift_date', 'シフト日付', 'required|valid_date[Y-m-d]');
        $val->add_field('start_time', '開始時刻', 'required');
        $val->add_field('end_time', '終了時刻', 'required');
        $val->add_field('recruit_count', '募集人数', 'required|numeric_min[1]|numeric_max[100]');
        $val->add_field('free_text', '備考', 'max_length[500]');
        
        // 開始時刻と終了時刻の論理チェック
        $val->add_callable('Model_Shifts');
        $val->add_rule('validate_time_order', 'start_time', 'end_time');

        return $val;
    }

    /**
     * シフト更新用バリデーションルールを定義
     */
    public static function validate_update($factory = 'update')
    {
        $val = \Validation::forge($factory);
        $val->add_field('shift_date', 'シフト日付', 'valid_date[Y-m-d]');
        $val->add_field('start_time', '開始時刻', '');
        $val->add_field('end_time', '終了時刻', '');
        $val->add_field('recruit_count', '募集人数', 'numeric_min[1]|numeric_max[100]');
        $val->add_field('free_text', '備考', 'max_length[500]');
        
        // 開始時刻と終了時刻の論理チェック
        $val->add_callable('Model_Shifts');
        $val->add_rule('validate_time_order', 'start_time', 'end_time');

        return $val;
    }
    
    /**
     * カスタムバリデーションルール：終了時刻が開始時刻より後であることを確認
     */
    public static function _validation_is_later_than_start_time($val, $field)
    {
        // 既存のバリデーションロジック
        if ($val && \Input::param($field) && $val <= \Input::param($field)) {
            return false;
        }
        return true;
    }

    /**
     * 新規シフト作成
     */
    public static function create_shift(array $data)
    {
        $shift = static::forge($data);
        if ($shift->save()) {
            return $shift;
        }
        return false;
    }

    /**
     * シフトを更新する
     */
    public static function update_shift($id, array $data)
    {
        $shift = static::find($id);
        if (!$shift) {
            return false;
        }
        
        // 新しい募集人数が現在の参加者数を下回らないかチェック
        if (isset($data['recruit_count'])) {
            $current_participants = \Model_Shift_Assignments::count_participants_for_shift($id);
            if ($data['recruit_count'] < $current_participants) {
                throw new Exception('定員を現在の参加者数より少なくすることはできません');
            }
        }
        
        $shift->set($data);
        if ($shift->save()) {
            return $shift;
        }
        return false;
    }

    /**
     * シフトを削除する
     */
    public static function delete_shift($id)
    {
        $shift = static::find($id);
        if ($shift) {
            // cascade_delete設定により、関連するshift_assignmentsも自動的に削除される
            return $shift->delete();
        }
        return false;
    }
}