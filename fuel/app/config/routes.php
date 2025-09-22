<?php
return [
  // UI（表示系）
  '_root_'            => 'users/login',        // トップ＝ログイン画面
  'users/login'       => 'users/login',        // ログイン画面
  'shifts'            => 'shifts/index',
  'shifts/create'     => 'shifts/create',
  'shifts/(:num)'     => 'shifts/view/$1',
  'users/create'      => 'users/create',
  'users/edit/(:num)' => 'users/edit/$1',      // ユーザー編集画面
  'users/update/(:num)' => 'users/update/$1',  // ユーザー更新
  'users/delete/(:num)' => 'users/delete/$1',  // ユーザー削除
  'my/shifts'         => 'shifts/my',           // 自分のシフト一覧（必要なら）

  // API（JSON）
  'api/shifts'                    => 'api/shifts/index',       // GET一覧 / POST作成
  'api/shifts/(:num)'             => 'api/shifts/show/$1',     // GET詳細 / PUT更新 / DELETE削除
  'api/shifts/(:num)/join'        => 'api/shift/assignments/join/$1',   // POST参加
  'api/shifts/(:num)/cancel'      => 'api/shift/assignments/cancel/$1', // POSTキャンセル
  'api/shift_assignments'         => 'api/shift/assignments/index',     // GET一覧
  'api/users'                     => 'api/users/index',        // GET/POST
  'api/users/(:num)'              => 'api/users/show/$1',      // GET/PUT/DELETE
  'api/debug/db-test'           => 'api/debug/db_test',
];