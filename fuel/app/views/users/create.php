<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>ユーザー作成</title></head>
<body>
  <h1>ユーザー作成</h1>

  <?php if (!empty($error)): ?>
    <p style="color:red"><?= e($error) ?></p>
  <?php endif; ?>

  <form method="post" action="/users/create">
    <label>名前: <input type="text" name="name" required></label><br>
    <label>色: <input type="color" name="color" value="#000000"></label><br>
    <button type="submit">ログイン / 新規作成</button>
  </form>
</body>
</html>