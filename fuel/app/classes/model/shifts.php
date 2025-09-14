<?php

class Model_Shifts extends \Orm\Model
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
}