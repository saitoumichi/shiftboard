<?php echo \Fuel\Core\View::forge('template', array(
    'title' => 'マイシフト',
    'content' => \Fuel\Core\View::forge('shift_assignments/my_assignments_content', array(
        'assignments' => $assignments,
        'current_user_id' => $current_user_id,
    ))
)); ?>
