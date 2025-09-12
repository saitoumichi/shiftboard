<?php
namespace Fuel\Migrations;

class Create_shifts
{
    public function up()
    {
        \DBUtil::create_table('shifts', [
            'id'            => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'bigint', 'unsigned' => true, 'null' => true],
            'shift_date'    => ['type' => 'date'],
            'start_time'    => ['type' => 'time'],
            'end_time'      => ['type' => 'time'],
            'recruit_count' => ['type' => 'int', 'unsigned' => true, 'default' => 1],
            'free_text'     => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'created_at'    => ['type' => 'timestamp', 'default' => \DB::expr('CURRENT_TIMESTAMP')],
            'updated_at'    => ['type' => 'timestamp', 'null' => true],
        ], ['id'], false, 'InnoDB', 'utf8mb4');

        // インデックス & 外部キー
        \DB::query("ALTER TABLE `shifts` ADD INDEX (`shift_date`)")->execute();
        \DB::query("ALTER TABLE `shifts` ADD INDEX (`start_time`)")->execute();
        \DB::query("ALTER TABLE `shifts` ADD INDEX (`end_time`)")->execute();
        \DB::query("
            ALTER TABLE `shifts`
            ADD CONSTRAINT `fk_shifts_user`
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
            ON DELETE SET NULL
        ")->execute();
    }

    public function down()
    {
        \DB::query("ALTER TABLE `shifts` DROP FOREIGN KEY `fk_shifts_user`")->execute();
        \DBUtil::drop_table('shifts');
    }
}