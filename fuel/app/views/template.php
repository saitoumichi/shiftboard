<?php use Fuel\Core\Uri; use Fuel\Core\Session; ?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title : 'シフトボード' ?></title>
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= Uri::create('css/transparent-override.css') ?>?v=<?= time() ?>">
    </head>
<body style="font-family: system-ui, sans-serif; padding: 24px">
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
    
    <?php
        $user_id = Session::get('user_id');
        $user = $user_id ? \Model_User::find($user_id) : null;
    ?>

    <div class="header">
        <div class="header-left">
            <h1>シフトボード</h1>
            <?php if ($user): ?>
                <a href="<?= \Fuel\Core\Uri::create('users/logout') ?>" class="logout-btn">ログアウト</a>
            <?php endif; ?>
        </div>
        <div class="nav-links">
            <a href="<?= \Fuel\Core\Uri::create('shifts') ?>">シフト一覧</a>
            <?php if ($user): ?>
                <a href="<?= \Fuel\Core\Uri::create('shifts/create') ?>">シフト作成</a>
                <a href="<?= \Fuel\Core\Uri::create('my/shifts') ?>">自分のシフト</a>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <?php if ($user): ?>
                <span class="username"><?= htmlspecialchars($user->name) ?> さん</span>
            <?php else: ?>
                <a href="<?= \Fuel\Core\Uri::create('users/create') ?>" class="login-btn">ユーザー登録・ログイン</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?= $content ?>
</body>
</html>