<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト詳細 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- シフト詳細ページ専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shifts-view.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>
    
    <!-- シフト詳細ページ専用JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/shifts-view.js'); ?>"></script>
</head>
<body>
    <!-- HTMLテンプレートを読み込み -->
    <?php include APPPATH . 'views/templates/shifts/view.html'; ?>
</body>
</html>
