<?php
class Model_User extends \Orm\Model
{
    protected static $_table_name  = 'users';
    protected static $_primary_key = ['id'];

    protected static $_properties  = array(
        'id',
        'name',
        'color',
        'created_at',
        'updated_at',
    );

        // ユーザー 1 : N 割り当て
        protected static $_has_many = [
            'assignments' => [
                'key_from'   => 'id',
                'model_to'   => 'Model_Shift_Assignment',
                'key_to'     => 'user_id',
                'cascade_save'   => false,
                'cascade_delete' => false,
            ],
        ];

}