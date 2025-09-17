<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title : 'シフトボード' ?></title>
    <link rel="stylesheet" href="<?= \Fuel\Core\Uri::create('assets/css/common.css') ?>">
    </head>
<body style="font-family: system-ui, sans-serif; padding: 24px">
    <?php
        $user = \Controller_Users::get_logged_in_user();
    ?>

    <div class="header">
        <h1>シフトボード</h1>
        <div class="nav-links">
            <a href="<?= \Fuel\Core\Uri::create('shifts') ?>">シフト一覧</a>
            <?php if ($user): ?>
                <a href="<?= \Fuel\Core\Uri::create('shifts/create') ?>">シフト作成</a>
                <a href="<?= \Fuel\Core\Uri::create('shift_assignments/my_assignments') ?>">自分のシフト</a>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <?php if ($user): ?>
                <span class="username"><?= htmlspecialchars($user->name) ?> さん</span>
                <a href="<?= \Fuel\Core\Uri::create('users/logout') ?>" class="logout-btn">ログアウト</a>
            <?php else: ?>
                <a href="<?= \Fuel\Core\Uri::create('users/create') ?>" class="login-btn">ユーザー登録・ログイン</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?= $content ?>
</body>
</html>