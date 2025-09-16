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
}