<?php
return [
  '_root_' => 'shifts/index',     // 一覧をトップに
  '_404_'  => 'welcome/404',

  // ページ（KOが読み込まれるView）
  'shifts'              => 'shifts/index',     // 一覧（KOで非同期取得）
  'shifts/create'       => 'shifts/create',
  'shifts/(:num)'       => 'shifts/view/$1',
  'my/shifts'           => 'myshifts/index',
  'members'             => 'members/index',    // メンバー一覧
  'members/create'      => 'members/create',   // メンバー作成
  'members/(:num)'      => 'members/view/$1',  // メンバー詳細

  // API (JSON)
  'api/shifts'                    => 'api/shifts/index',     // GET一覧 / POST作成
  'api/shifts/(:num)'             => 'api/shifts/show/$1',   // GET詳細
  'api/shifts/(:num)/update'      => 'api/shifts/update/$1',
  'api/shifts/(:num)/delete'      => 'api/shifts/delete/$1',
  'api/shifts/(:num)/join'        => 'api/shifts/join/$1',   // 参加
  'api/shifts/(:num)/cancel'      => 'api/shifts/cancel/$1', // 取消
  
  'api/user-shifts'               => 'api/user_shifts/index',
  'api/user-shifts/(:num)'        => 'api/user_shifts/show/$1',
  'api/user-shifts/create'        => 'api/user_shifts/create',
  'api/user-shifts/(:num)/update' => 'api/user_shifts/update/$1',
  'api/user-shifts/(:num)/delete' => 'api/user_shifts/delete/$1',
  
  'api/my/shifts'                 => 'api/myshifts/index',   // ログインユーザーの一覧
  
  // メンバー管理API
  'api/members'                   => 'api/members/index',    // GET一覧 / POST作成
  'api/members/(:num)'            => 'api/members/show/$1',  // GET詳細
  'api/members/(:num)/update'     => 'api/members/update/$1', // PUT更新
  'api/members/(:num)/delete'     => 'api/members/delete/$1', // DELETE削除
  
  // デバッグ用
  'api/debug/db-test'             => 'api/debug/db_test',
];