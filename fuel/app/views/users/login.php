<?php use Fuel\Core\Uri; use Fuel\Core\Session; ?>
<!DOCTYPE html>
<html>
<head>
  <meta name="current-user-id" content="0">
  <meta name="api-base" content="/api">
  <meta charset="utf-8">
  <title>ログイン / ユーザー登録</title>
  
  <script>
    window.API_BASE = '/api';
    window.CURRENT_USER_ID = 0; // ログインページでは常に0
  </script>
  
  <link rel="stylesheet" href="<?php echo Uri::create('css/common.css'); ?>">
  <style>
    .card{max-width:440px;margin:40px auto;padding:24px;border:1px solid #ddd;border-radius:8px}
    .row{margin-bottom:12px}
    label{display:block;font-weight:600;margin-bottom:6px}
    input[type=text],input[type=color]{width:100%;padding:8px;box-sizing:border-box}
    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}
    .btn{padding:8px 14px;border:1px solid #333;background:#333;color:#fff;border-radius:4px;cursor:pointer}
    .btn.secondary{background:#fff;color:#333}
    .flash{color:#b00020;margin-bottom:12px}
  </style>
</head>
<body>
  <h1>ユーザー登録 / ログイン</h1>

  <?php if ($msg = Session::get_flash('error')): ?>
    <div class="flash"><?php echo e($msg); ?></div>
  <?php endif; ?>

  <form method="post" action="<?php echo Uri::create('users/login'); ?>">
    <div class="row">
      <label for="name">ユーザー名（必須）</label>
      <input id="name" name="name" type="text" placeholder="例）みちこ" required>
    </div>
    <div class="row">
      <label for="color">カラー</label>
      <input id="color" name="color" type="color" value="#000000">
    </div>
    <div class="actions">
      <button class="btn secondary" type="reset">クリア</button>
      <button class="btn" type="submit">はじめる</button>
    </div>
  </form>
</body>
</html>