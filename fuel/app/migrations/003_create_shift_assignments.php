<?php
namespace Fuel\Migrations;

class Create_shift_assignments
{
    public function up()
    {
        \DBUtil::create_table('shift_assignments', [
            'id'         => ['type' => 'bigint', 'unsigned' => true, 'auto_increment' => true],
            'shift_id'   => ['type' => 'bigint', 'unsigned' => true],
            'user_id'    => ['type' => 'bigint', 'unsigned' => true],
            'self_word'  => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'status'     => ['type' => 'enum', 'constraint' => ['assigned','confirmed','cancelled'], 'default' => 'assigned'],
            'created_at' => ['type' => 'timestamp', 'default' => \DB::expr('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'timestamp', 'null' => true],
        ], ['id'], false, 'InnoDB', 'utf8mb4');

        // ユニーク: 同一ユーザーは同一シフトに1件
        \DB::query("ALTER TABLE `shift_assignments` ADD UNIQUE KEY `uniq_shift_user` (`shift_id`,`user_id`)")->execute();

        // 外部キー
        \DB::query("
          ALTER TABLE `shift_assignments`
          ADD CONSTRAINT `fk_sa_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE CASCADE
        ")->execute();
        \DB::query("
          ALTER TABLE `shift_assignments`
          ADD CONSTRAINT `fk_sa_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ")->execute();
    }

    public function down()
    {
        \DB::query("ALTER TABLE `shift_assignments` DROP FOREIGN KEY `fk_sa_shift`")->execute();
        \DB::query("ALTER TABLE `shift_assignments` DROP FOREIGN KEY `fk_sa_user`")->execute();
        \DBUtil::drop_table('shift_assignments');
    }
}