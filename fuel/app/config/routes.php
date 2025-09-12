<?php
return [
  '_root_'  => 'api/shifts',
  '_404_'   => 'shifts/404',

  // Shifts CRUD
  'api/shifts'              => 'api/shifts/index',      // GET:一覧 / POST:作成
  'api/shifts/(:num)'       => 'api/shifts/show/$1',    // GET:詳細
  'api/shifts/(:num)/update'=> 'api/shifts/update/$1',  // PUT:更新
  'api/shifts/(:num)/delete'=> 'api/shifts/delete/$1',  // DELETE:削除

  // 参加者一覧（任意）
  'api/shifts/(:num)/assignments' => 'api/shifts/assignments/$1',
];