<?php use Fuel\Core\Uri; use Fuel\Core\Session; ?>
<!DOCTYPE html>
<html>
<head>
    <meta name="current-user-id" content="<?= (int)($current_user_id ?? 0) ?>">
    <meta name="api-base" content="/api">
    <meta charset="utf-8">
    <title>自分のシフト - ShiftBoard</title>
    
    <script>
      window.API_BASE = '/api';
      window.CURRENT_USER_ID = Number(
        document.querySelector('meta[name="current-user-id"]')?.content || 0
      );
    </script>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo Uri::create('css/common.css'); ?>">
    
    <!-- 自分のシフトページ専用CSS -->
    <link rel="stylesheet" href="<?php echo Uri::create('css/myshifts.css'); ?>">
    
    <!-- jQuery -->
    <script src="<?php echo Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- Knockout.js -->
    <script src="<?php echo Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- ベースURL設定 -->
    <script>window.APP_BASE="<?php echo Uri::base(false); ?>";</script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo Uri::create('js/common.js'); ?>"></script>
    
    <!-- 自分のシフトページ専用JavaScript -->
    <script src="<?php echo Uri::create('js/myshifts.js'); ?>"></script>
</head>
<body>
    <!-- HTMLテンプレートを読み込み -->
    <?php include APPPATH . 'views/templates/myshifts/index.html'; ?>
</body>
</html>
