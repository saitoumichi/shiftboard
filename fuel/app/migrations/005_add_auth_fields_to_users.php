<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

class Add_auth_fields_to_users
{
    public function up()
    {
        // 1) 列を追加
        DBUtil::add_fields('users', array(
            'email' => array('type' => 'varchar', 'constraint' => 191, 'null' => false),
            'password_hash' => array('type' => 'varchar', 'constraint' => 255, 'null' => false),
        ));

        // 2) email に UNIQUE インデックスを付与
        // 第2引数はインデックス名（任意の名前でOK）
        DBUtil::create_index('users', array('email'), 'uniq_users_email', 'unique');
        // Fuelの版によっては引数順が (table, index, columns, type) の場合もあるため
        // もしエラーになったら: DBUtil::create_index('users', 'uniq_users_email', array('email'), 'unique');
    }

    public function down()
    {
        // 逆順で削除
        // create_index の引数順に合わせて drop も同様に
        DBUtil::drop_index('users', 'uniq_users_email');
        DBUtil::drop_fields('users', array('email', 'password_hash'));
    }
}