<!doctype html>
<html>
<head><meta charset="utf-8"><title>ログイン</title></head>
<body>
  <h1>ログイン</h1>
  <?php if (!empty($error)): ?><p style="color:red;"><?= e($error) ?></p><?php endif; ?>

  <form method="post" action="/users/login">
    <label>既存ユーザーでログイン：</label>
    <select name="user_id">
      <?php foreach (($users ?? []) as $u): ?>
        <option value="<?= (int)$u->id ?>">
          #<?= (int)$u->id ?> <?= e($u->name) ?> (<?= e($u->color) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit">ログイン</button>
  </form>

  <hr>
  <a href="/users/create">→ 新規ユーザーを作成</a>
</body>
</html>