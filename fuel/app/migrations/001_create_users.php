<?php
namespace Fuel\Migrations;

class Create_users
{
    public function up()
    {
        \Fuel\Core\DBUtil::create_table('users', array(
            'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
            'name' => array('constraint' => 100, 'type' => 'varchar'),
            'color' => array('constraint' => 7, 'type' => 'char', 'null' => true),
            'created_at' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
            'updated_at' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'null' => true),
        ), array('id'));
    }

    public function down()
    {
        \Fuel\Core\DBUtil::drop_table('users');
    }
}