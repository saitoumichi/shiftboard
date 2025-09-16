<?php

return [
  // UI
  '_root_'        => 'shifts/index',
  'users'         => 'users/index',
  'users/create'  => 'users/create',
  'users/(:num)'  => 'users/view/$1',
  'my/shifts' => 'shift/assignment/my_assignments',
  'myshifts'  => 'shift/assignment/my_assignments',
  'shifts'  => 'shifts/index',
  'shifts/create' => 'shifts/create',
  'shifts/(:num)' => 'shifts/view/$1',

  // API
  'api/shifts'          => 'api/shifts/index',    // GET一覧 / POST作成
  'api/shifts/(:num)'   => 'api/shifts/show/$1',  // GET詳細 / PUT更新 / DELETE削除
  'api/shifts/(:num)/join' => 'api/shift_assignments/join/$1',  // POST参加
  'api/shifts/(:num)/cancel' => 'api/shift_assignments/cancel/$1',  // POSTキャンセル
  'api/shift_assignments' => 'api/shift_assignments/index',  // GET一覧
  'api/users'           => 'api/users/index',     // GET一覧 / POST作成
  'api/users/(:num)'    => 'api/users/show/$1',   // GET詳細 / PUT更新 / DELETE削除
  'api/debug/db-test' => 'api/debug/db_test',
];