<?php
namespace Fuel\Migrations;

class Create_shift_assignments
{
    public function up()
    {
        \DBUtil::create_table('shift_assignments', array(
            'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
            'shift_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
            'user_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
            'self_word' => array('constraint' => 500, 'type' => 'varchar', 'null' => true),
            'status' => array('type' => 'enum', 'constraint' => "'assigned','confirmed','cancelled'", 'default' => 'assigned'),
            'created_at' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
            'updated_at' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'null' => true),
        ), array('id'));

        \DBUtil::create_index('shift_assignments', 'shift_id', 'shift_id');
        \DBUtil::create_index('shift_assignments', 'user_id', 'user_id');
    }

    public function down()
    {
        \DBUtil::drop_table('shift_assignments');
    }
}