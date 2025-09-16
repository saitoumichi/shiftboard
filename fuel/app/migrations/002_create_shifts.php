<?php
namespace Fuel\Migrations;

class Create_shifts
{
    public function up()
    {
        \Fuel\Core\DBUtil::create_table('shifts', array(
            'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
            'created_by' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'null' => false),
            'shift_date' => array('type' => 'date', 'null' => false),
            'start_time' => array('type' => 'time', 'null' => false),
            'end_time' => array('type' => 'time', 'null' => false),
            'recruit_count' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'default' => 1, 'null' => false),
            'free_text' => array('constraint' => 500, 'type' => 'varchar', 'null' => true),
            'created_at' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
            'updated_at' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'null' => true),
        ), array('id'));
        \Fuel\Core\DBUtil::create_index('shifts', 'created_by', 'created_by');
    }

    public function down()
    {
        \Fuel\Core\DBUtil::drop_table('shifts');
    }
}