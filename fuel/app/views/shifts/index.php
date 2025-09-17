<?php use Fuel\Core\Uri;?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
    <title>シフト一覧</title>

    <!-- 画面→JSへの受け渡し（meta） -->
    <meta name="current-user-id" content="<?= (int)($current_user_id ?? 0) ?>">
    <script>
  window.API_BASE = '/api';
  window.CURRENT_USER_ID = Number(
    document.querySelector('meta[name="current-user-id"]')?.content || 0
  );
</script>
    <meta name="api-base" content="/api">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>">
    <link rel="stylesheet" href="/css/shifts.css">

    <!-- ライブラリ（defer 推奨） -->
    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/knockout-min.js"></script>

    <!-- 共通JS（ここで CURRENT_USER_ID を“読むだけ”にする） -->
    <script src="/js/common.js"></script>

    <!-- ページ専用JS（ShiftViewModelの定義＆applyBindingsをここで実行） -->
    <script src="/js/shifts.js"></script>

    <!-- 追加：ベースURL（必要なら） -->
    <script>
      // deferでも先に評価させたい軽量値だけここでOK
      window.APP_BASE = "<?= \Fuel\Core\Uri::base(false); ?>";
    </script>
</head>
<body>
    <?php include APPPATH.'views/templates/shifts/index.html'; ?>
</body>
</html>