<?php

class Model_Shift extends \Orm\Model
{
    protected static $_table_name = 'shifts';
    protected static $_primary_key = array('id');

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

    // シフト 1 : N 割り当て
    protected static $_has_many = [
        'assignments' => [
            'key_from'   => 'id',
            'model_to'   => 'Model_Shift_Assignment',
            'key_to'     => 'shift_id',
            'cascade_save'   => false,
            'cascade_delete' => false,
        ],
    ];

    protected static $_observers = [
        'Orm\\Observer_CreatedAt' => [
            'events' => ['before_insert'],
            'mysql_timestamp' => true,   // DATETIME/TIMESTAMP を自動セット
        ],
        'Orm\\Observer_UpdatedAt' => [
            'events' => ['before_update'],
            'mysql_timestamp' => true,
        ],
    ];

}