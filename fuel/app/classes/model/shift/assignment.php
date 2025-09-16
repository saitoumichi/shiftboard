<?php

class Model_Shift_Assignment extends \Orm\Model
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


    // N : 1 ユーザー
    protected static $_belongs_to = [
      'user' => [
          'key_from'   => 'user_id',
          'model_to'   => 'Model_User',
          'key_to'     => 'id',
          'cascade_save'   => false,
          'cascade_delete' => false,
      ],
      'shift' => [
          'key_from'   => 'shift_id',
          'model_to'   => 'Model_Shift',
          'key_to'     => 'id',
          'cascade_save'   => false,
          'cascade_delete' => false,
      ],
  ];
}