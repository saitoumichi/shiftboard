<?php
return array (
  'version' => array(  
    'app' => array(    
      'default' => array(      
        0 => '001_create_users',
        1 => '002_create_shifts',
        2 => '003_create_shift_assignments',
        3 => '004_fix_shift_assignments_status_null',
        4 => '005_add_auth_fields_to_users',
      ),
    ),
    'module' => array(    
    ),
    'package' => array(    
    ),
  ),
  'folder' => 'migrations/',
  'table' => 'migration',
  'flush_cache' => false,
  'flag' => NULL,
);
