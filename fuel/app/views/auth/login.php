<!doctype html>
<html>
<head><meta charset="utf-8"><title>ログイン</title></head>
<body>
  <h1>ログイン</h1>
  <?php if ($msg = \Session::get_flash('error')): ?>
    <div style="color:red"><?= e($msg) ?></div>
  <?php endif; ?>
  <form action="/users/login" method="post">
    <label>名前 <input type="text" name="name" required></label><br>
    <label>色   <input type="color" name="color" value="#000000"></label><br>
    <button type="submit">ログイン / 新規作成</button>
  </form>
</body>
</html>