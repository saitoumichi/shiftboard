<?php use Fuel\Core\Uri; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト詳細 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>">
    
    <!-- シフト詳細ページ専用CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/shifts-view.css') ?>">
    
    <!-- jQuery -->
    <script src="<?= Uri::create('js/jquery-3.6.0.min.js') ?>"></script>
    
    <!-- Knockout.js -->
    <script src="<?= Uri::create('js/knockout-min.js') ?>"></script>
    
    <!-- ベースURL設定 -->
    <script>window.APP_BASE="<?= Uri::base(false) ?>";</script>
    
    <!-- 共通JavaScript -->
    <script src="<?= Uri::create('js/common.js') ?>"></script>
    
    <!-- シフト詳細ページ専用JavaScript -->
    <script src="<?= Uri::create('js/shifts-view.js') ?>"></script>
</head>
<body>
    <!-- HTMLテンプレートを読み込み -->
    <?php include APPPATH . 'views/templates/shifts/view.html'; ?>
</body>
</html>
