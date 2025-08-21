<?php
return [
  'api/v1/shifts'               => 'api/v1/shifts/index',
  'api/v1/shifts/(:num)'        => 'api/v1/shifts/show/$1',
  'api/v1/shifts/(:num)/apply'  => 'api/v1/shifts/apply/$1',
  'api/v1/shifts/(:num)/cancel' => 'api/v1/shifts/cancel/$1',

  // 互換（古いURLが混在していたため）
  'api/shifts'        => 'api/v1/shifts/index',
  'api/shifts/list'   => 'api/v1/shifts/index',
  'api/shifts/create' => 'api/v1/shifts/index',
];