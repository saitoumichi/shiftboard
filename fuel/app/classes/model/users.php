<?php
class Model_User extends Orm\Model
{
    protected static $_table_name  = 'users';
    protected static $_primary_key = ['id'];

    protected static $_properties  = [
        'id',
        'name',
        'colors',
        'created_at',
        'updated_at',
    ];

    protected static $_has_many = [
        'owned_shifts' => [
            'model_to' => 'Model_Shift',
            'key_from' => 'id',
            'key_to'   => 'user_id',
        ],
        'assignments' => [
            'model_to' => 'Model_Shifts_Assignment',
            'key_from' => 'id',
            'key_to'   => 'user_id',
        ],
    ];

    protected static $_observers = [
        'Orm\\Observer_Typing'   => ['events' => ['before_save']],
        'Orm\\Observer_CreatedAt'=> ['events' => ['before_insert'], 'mysql_timestamp' => true],
        'Orm\\Observer_UpdatedAt'=> ['events' => ['before_save'],   'mysql_timestamp' => true],
    ];
}