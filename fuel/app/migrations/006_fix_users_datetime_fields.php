<?php
namespace Fuel\Migrations;

class Fix_users_datetime_fields
{
    public function up()
    {
        // created_atとupdated_atをdatetime型に変更
        \Fuel\Core\DB::query("ALTER TABLE users MODIFY COLUMN created_at DATETIME NOT NULL")->execute();
        \Fuel\Core\DB::query("ALTER TABLE users MODIFY COLUMN updated_at DATETIME NULL")->execute();
    }

    public function down()
    {
        // ロールバック: int型に戻す
        \Fuel\Core\DB::query("ALTER TABLE users MODIFY COLUMN created_at INT UNSIGNED NOT NULL")->execute();
        \Fuel\Core\DB::query("ALTER TABLE users MODIFY COLUMN updated_at INT UNSIGNED NULL")->execute();
    }
}
