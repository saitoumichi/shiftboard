<?php
class Model_Users extends Orm\Model
{
    protected static $_table_name  = 'users';
    protected static $_primary_key = ['id'];

    protected static $_properties  = array(
        'id',
        'name',
        'colors',
        'created_at',
        'updated_at',
    );

    protected static $_observers = array(
        'Orm\\Observer_CreatedAt' => array(
            'events' => array('before_insert'),
            'mysql_timestamp' => true,
        ),
        'Orm\\Observer_UpdatedAt' => array(
            'events' => array('before_update'),
            'mysql_timestamp' => true,
        ),
    );

        /**
     * リレーション定義
     * ユーザーが作成したシフトと、参加したシフトアサインメントを定義
     */
    protected static $_has_many = array(
        'shifts' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Shifts',
            'key_to' => 'created_by',
            'cascade_delete' => true,
        ),
        'assignments' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Shift_Assignments',
            'key_to' => 'user_id',
            'cascade_delete' => true,
        ),
    );

        /**
     * ユーザー登録用バリデーションルール
     */
    public static function validate_create($factory)
    {
        $val = \Validation::forge($factory);
        $val->add_field('name', 'ユーザー名', 'required|min_length[1]|max_length[100]');
        $val->add_field('color', 'カラー', 'valid_string[alpha,numeric]|exact_length[6]');
        return $val;
    }

}