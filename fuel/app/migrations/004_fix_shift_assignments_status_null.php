<?php
namespace Fuel\Migrations;

class Fix_shift_assignments_status_null
{
    public function up()
    {
        // 既存のNULLのstatusを'assigned'に更新
        \Fuel\Core\DB::update('shift_assignments')
            ->set(array('status' => 'assigned'))
            ->where('status', null)
            ->execute();
            
        // カラムをNOT NULLに変更（既にDEFAULT 'assigned'が設定されているため安全）
        \Fuel\Core\DB::query("ALTER TABLE shift_assignments MODIFY COLUMN status ENUM('assigned','confirmed','cancelled') NOT NULL DEFAULT 'assigned'")->execute();
    }

    public function down()
    {
        // カラムをNULL許可に戻す
        \Fuel\Core\DB::query("ALTER TABLE shift_assignments MODIFY COLUMN status ENUM('assigned','confirmed','cancelled') NULL DEFAULT 'assigned'")->execute();
    }
}
