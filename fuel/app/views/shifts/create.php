<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト作成 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- シフト作成ページ専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shifts-create.css'); ?>">
    
    <!-- jQuery -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- ベースURL設定 -->
    <script>window.APP_BASE="<?php echo \Fuel\Core\Uri::base(false); ?>";</script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>
    
    <!-- シフト作成ページ専用JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/shifts-create.js'); ?>"></script>
</head>
<body>
    <!-- HTMLテンプレートを読み込み -->
    <?php include APPPATH . 'views/templates/shifts/create.html'; ?>
</body>
</html>
