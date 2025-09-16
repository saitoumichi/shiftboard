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

    protected static $_has_many = array(
        'assignments' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Shift_Assignment',
            'key_to' => 'shift_id',
            'cascade_save' => true,
            'cascade_delete' => false,
        )
    );
}