<?php
return [
  '_root_' => 'shifts/index',     // 一覧をトップに
  '_404_'  => 'welcome/404',

  // ページ（KOが読み込まれるView）
  'shifts'              => 'shifts/index',     // 一覧（KOで非同期取得）
  'shifts/create'       => 'shifts/create',
  'shifts/(:num)'       => 'shifts/view/$1',
  'my/shifts'           => 'myshifts/index',

  // API (JSON)
  'api/shifts'                    => 'api/shifts/index',     // GET一覧 / POST作成
  'api/shifts/(:num)'             => 'api/shifts/show/$1',   // GET/PUT/DELETE
  'api/shifts/(:num)/update'      => 'api/shifts/update/$1',
  'api/shifts/(:num)/delete'      => 'api/shifts/delete/$1',
  
  'api/user-shifts'               => 'api/user_shifts/index',
  'api/user-shifts/(:num)'        => 'api/user_shifts/show/$1',
  'api/user-shifts/create'        => 'api/user_shifts/create',
  'api/user-shifts/(:num)/update' => 'api/user_shifts/update/$1',
  'api/user-shifts/(:num)/delete' => 'api/user_shifts/delete/$1',
  
  'api/my/shifts'                 => 'api/myshifts/index',   // ログインユーザーの一覧
];