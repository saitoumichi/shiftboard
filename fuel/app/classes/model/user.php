<?php

class Model_User extends \Orm\Model
{
    protected static $_table_name = 'users';

    protected static $_primary_key = ['id'];

    protected static $_properties = [
        'id',
        'name',
        'color',
        'created_at',
        'updated_at',
    ];

    protected static $_has_many = [
        'created_shifts' => [
            'key_from'   => 'id',
            'model_to'   => 'Model_Shift',
            'key_to'     => 'created_by',
            'cascade_save'   => false,
            'cascade_delete' => false,
        ],
        'assignments' => [
            'key_from'   => 'id',
            'model_to'   => 'Model_Shift_Assignment',
            'key_to'     => 'user_id',
            'cascade_save'   => false,
            'cascade_delete' => false,
        ],
    ];
    
    protected static $_observers = [
        'Orm\\Observer_CreatedAt' => [
            'events' => ['before_insert'],
            'mysql_timestamp' => true,
        ],
        'Orm\\Observer_UpdatedAt' => [
            'events' => ['before_update'],
            'mysql_timestamp' => true,
        ],
    ];
    
    public static function validate($context = 'create')
    {
        $val = \Fuel\Core\Validation::forge();
        $val->add_field('name',  '名前', 'required|min_length[1]|max_length[50]');
        $val->add('color', '色'); // 必須にしない例
        return $val;
    }
}