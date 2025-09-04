<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト一覧 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- シフト専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shifts.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>
    
    <!-- シフト一覧ページ専用JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/shifts.js'); ?>"></script>
</head>
<body>
    <!-- HTMLテンプレートを読み込み -->
    <?php include APPPATH . 'views/templates/shifts/index.html'; ?>
    
    <script>
        // シフト一覧ページの初期化
        $(document).ready(function() {
            // ShiftViewModelを適用
            ko.applyBindings(new ShiftViewModel());
        });
    </script>
</body>
</html>
