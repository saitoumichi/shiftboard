<?php use Fuel\Core\Uri; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト作成 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>?v=<?= time() ?>">
    
    <!-- シフト作成ページ専用CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/shifts-create.css') ?>?v=<?= time() ?>">
    
    <!-- 半透明効果CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/transparent-override.css') ?>?v=<?= time() ?>">
    
    <!-- jQuery -->
    <script src="<?= Uri::create('js/jquery-3.6.0.min.js') ?>"></script>
    
    <!-- ベースURL設定 -->
    <script>window.APP_BASE="<?= Uri::base(false) ?>";</script>
    
    <!-- ユーザーIDとAPIベースURL設定 -->
    <meta name="current-user-id" content="<?= (int)($current_user_id ?? 0) ?>">
    <meta name="api-base" content="/api">
    <script>
        window.API_BASE = '/api';
        window.CURRENT_USER_ID = Number(
            document.querySelector('meta[name="current-user-id"]')?.content || 0
        );
    </script>
    
    <!-- 共通JavaScript -->
    <script src="<?= Uri::create('js/common.js') ?>" defer></script>
    
    <!-- シフト作成ページ専用JavaScript -->
    <script src="<?= Uri::create('js/shifts-create.js') ?>" defer></script>
</head>
<body>
    <!-- 自然の要素 -->
    <div class="sakura-petals">
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌿</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌱</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌿</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌱</div>
    </div>
    
    <!-- HTMLテンプレートを読み込み -->
    <?php include APPPATH . 'views/templates/shifts/create.html'; ?>
</body>
</html>
