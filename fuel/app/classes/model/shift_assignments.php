<?php

class Model_Shift_Assignments extends \Orm\Model
{
    /**
     * テーブル名
     */
    protected static $_table_name = 'shift_assignments';
    
    /**
     * プライマリキー
     */
    protected static $_primary_key = array('id');
    
    /**
     * プロパティ定義
     */
    protected static $_properties = array(
        'id',
        'user_id',
        'shift_id',
        'status', // 'confirmed' or 'cancelled'
        'self_word',
        'created_at',
        'updated_at',
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
    );
    
    /**
     * リレーション定義
     */
    protected static $_belongs_to = array(
        'shift' => array(
            'key_from' => 'shift_id',
            'model_to' => 'Model_Shifts',
            'key_to' => 'id',
            'cascade_save' => false,
            'cascade_delete' => false,
        ),
        'user' => array(
            'key_from' => 'user_id',
            'model_to' => 'Model_Users',
            'key_to' => 'id',
            'cascade_save' => false,
            'cascade_delete' => false,
        ),
    );

    /**
     * ユーザーがシフトに参加できるかチェックする
     * @param int $user_id
     * @param int $shift_id
     * @return bool
     */
    public static function can_join_shift($user_id, $shift_id)
    {
        $shift = \Model_Shifts::find($shift_id);
        if (!$shift) {
            return false;
        }

        $current_participants = static::query()
            ->where('shift_id', $shift_id)
            ->where('status', '!=', 'cancelled')
            ->count();

        // 既に定員に達しているか、既に参加しているか
        if ($current_participants >= $shift->recruit_count || static::is_user_participating($user_id, $shift_id)) {
            return false;
        }

        return true;
    }

    /**
     * ユーザーが既にシフトに参加しているかチェックする
     * @param int $user_id
     * @param int $shift_id
     * @return bool
     */
    public static function is_user_participating($user_id, $shift_id)
    {
        return static::query()
            ->where('user_id', $user_id)
            ->where('shift_id', $shift_id)
            ->where('status', '!=', 'cancelled')
            ->count() > 0;
    }

    /**
     * ユーザーをシフトに参加させる
     * @param int $user_id
     * @param int $shift_id
     */
    public static function join_shift($user_id, $shift_id)
    {
        $assignment = static::query()
            ->where('user_id', $user_id)
            ->where('shift_id', $shift_id)
            ->get_one();

        if ($assignment) {
            $assignment->status = 'confirmed';
            $assignment->save();
        } else {
            $assignment = static::forge([
                'user_id' => $user_id,
                'shift_id' => $shift_id,
                'status' => 'confirmed',
            ]);
            $assignment->save();
        }
    }

    /**
     * ユーザーのシフト参加をキャンセルする
     * @param int $user_id
     * @param int $shift_id
     */
    public static function cancel_shift($user_id, $shift_id)
    {
        $assignment = static::query()
            ->where('user_id', $user_id)
            ->where('shift_id', $shift_id)
            ->where('status', '!=', 'cancelled')
            ->get_one();

        if ($assignment) {
            $assignment->status = 'cancelled';
            $assignment->save();
        }
    }

        /**
     * 指定されたシフトの現在の参加者数をカウントする
     */
    public static function count_participants_for_shift($shift_id)
    {
        return static::query()
            ->where('shift_id', $shift_id)
            ->where('status', '!=', 'cancelled')
            ->count();
    }
}