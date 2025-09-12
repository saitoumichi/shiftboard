<?php
class Model_Shifts_Assignment extends Orm\Model
{
    protected static $_table_name  = 'shifts_assignments';
    protected static $_primary_key = ['id'];

    protected static $_properties  = [
        'id',
        'shift_id',
        'user_id',
        'self_word',
        'status',
        'created_at',
        'updated_at',
    ];

    protected static $_belongs_to = [
        'shift' => [
            'model_to' => 'Model_Shift',
            'key_from' => 'shift_id',
            'key_to'   => 'id',
        ],
        'user' => [
            'model_to' => 'Model_User',
            'key_from' => 'user_id',
            'key_to'   => 'id',
        ],
    ];

    protected static $_observers = [
        'Orm\\Observer_Typing'   => ['events' => ['before_save']],
        'Orm\\Observer_CreatedAt'=> ['events' => ['before_insert'], 'mysql_timestamp' => true],
        'Orm\\Observer_UpdatedAt'=> ['events' => ['before_save'],   'mysql_timestamp' => true],
    ];
}