<?php

class Model_Shift_Assignments extends \Orm\Model
{
    protected static $_table_name = 'shift_assignments';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id',
        'user_id',
        'shift_id',
        'status', // 'confirmed' or 'cancelled'
        'self_word',
        'created_at',
        'updated_at',
    );
}