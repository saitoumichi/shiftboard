<?php

return [
  // UI
  '_root_'        => 'shifts/index',
  'users'         => 'users/index',
  'users/create'  => 'users/create',
  'users/(:num)'  => 'users/view/$1',

  // API
  'api/shifts'          => 'api/shifts/index',    // GET一覧 / POST作成
  'api/shifts/(:num)'   => 'api/shifts/show/$1',  // GET詳細 / PUT更新 / DELETE削除
  'api/users'           => 'api/users/index',     // GET一覧 / POST作成
  'api/users/(:num)'    => 'api/users/show/$1',   // GET詳細 / PUT更新 / DELETE削除
];