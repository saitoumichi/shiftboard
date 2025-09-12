<?php
namespace Fuel\Migrations;

class Create_users
{
    public function up()
    {
        \DBUtil::create_table('users', [
            'id'         => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'varchar', 'constraint' => 100],
            'colors'     => ['type' => 'char', 'constraint' => 7, 'null' => true],
            'created_at' => ['type' => 'timestamp', 'default' => \DB::expr('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'timestamp', 'null' => true],
        ], ['id'], false, 'InnoDB', 'utf8mb4');
    }

    public function down()
    {
        \DBUtil::drop_table('users');
    }
}