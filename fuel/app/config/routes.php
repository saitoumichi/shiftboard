<?php
return [
  // ==== Root & 404 ====
  // トップはシフト一覧ページにします（存在する Controller/Shifts を想定）
  '_root_'  => 'shifts/index',
  '_404_'   => '',  // 独自404が無ければ空のままでOK

  // ==== Pages (Views) ====
  'shifts'                              => 'shifts/index',
  'shifts/create'                       => 'shifts/create',
  'shifts/(:num)'                       => 'shifts/view/$1',
  'my/shifts'                           => 'myshifts/index',
  'users'                               => 'users/index',
  'users/create'                        => 'users/create',
  'users/(:num)'                        => 'users/view/$1',
  'shift_assignments'                   => 'shift_assignments/index',
  'shift_assignments/manage/(:num)'     => 'shift_assignments/manage/$1',
  'shift_assignments/my'                => 'shift_assignments/my_assignments',
  'shift_assignments/calendar'          => 'shift_assignments/calendar',

  // ==== API (JSON) ====
  // Shifts
  'api/shifts'                          => 'api/shifts/index',        // GET一覧 / POST作成
  'api/shifts/(:num)'                   => 'api/shifts/show/$1',      // GET詳細
  'api/shifts/(:num)/update'            => 'api/shifts/update/$1',    // PUT更新
  'api/shifts/(:num)/delete'            => 'api/shifts/delete/$1',    // DELETE削除
  'api/shifts/(:num)/join'              => 'api/shifts/join/$1',      // POST 参加
  'api/shifts/(:num)/cancel'            => 'api/shifts/cancel/$1',    // POST 取消

  // Shift Assignments
  'api/shift_assignments'               => 'api/shift_assignments/index',           // GET一覧 / POST作成
  'api/shift_assignments/(:num)'        => 'api/shift_assignments/show/$1',         // GET詳細
  'api/shift_assignments/(:num)/update' => 'api/shift_assignments/update/$1',       // PUT更新
  'api/shift_assignments/(:num)/delete' => 'api/shift_assignments/delete/$1',       // DELETE削除

  // Users
  'api/users'                            => 'api/users/index',         // GET一覧 / POST作成
  'api/users/(:num)'                     => 'api/users/show/$1',       // GET詳細
  'api/users/(:num)/update'              => 'api/users/update/$1',     // PUT更新
  'api/users/(:num)/delete'              => 'api/users/delete/$1',     // DELETE削除

  // Debug
  'api/debug/db-test'                    => 'api/debug/db_test',
];