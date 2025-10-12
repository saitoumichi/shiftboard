<?php use Fuel\Core\Uri; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト詳細 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>?v=<?= time() ?>">
    
    <!-- シフト詳細ページ専用CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/shifts-view.css') ?>?v=<?= time() ?>">
    
    <!-- 半透明効果CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/transparent-override.css') ?>?v=<?= time() ?>">
    
    <!-- jQuery -->
    <script src="<?= Uri::create('js/jquery-3.6.0.min.js') ?>"></script>
    
    <!-- Knockout.js -->
    <script src="<?= Uri::create('js/knockout-min.js') ?>"></script>
    
    <!-- ベースURL設定 -->
    <script>window.APP_BASE="<?= Uri::base(false) ?>";</script>
    
    <!-- ユーザーIDとAPIベースURL設定 -->
    <meta name="current-user-id" content="<?= (int)($current_user_id ?? 0) ?>">
    <meta name="shift-id" content="<?= (int)($shift_id ?? 0) ?>">
    <meta name="api-base" content="/api">
    <script>
        window.API_BASE = '/api';
        window.CURRENT_USER_ID = Number(
            document.querySelector('meta[name="current-user-id"]')?.content || 0
        );
        window.SHIFT_ID = <?= (int)($shift_id ?? 0) ?>;
    </script>
    
    <!-- 共通JavaScript -->
    <script src="<?= Uri::create('js/common.js') ?>" defer></script>
    
    <!-- シフト詳細ページ専用JavaScript -->
    <script src="<?= Uri::create('js/shifts-view.js') ?>" defer></script>
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
    <?php include APPPATH . 'views/templates/shifts/view.html'; ?>
</body>
</html>
