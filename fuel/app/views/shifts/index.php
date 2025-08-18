<h2>シフト一覧</h2>
<p><a href="/shifts/create">＋ 新規シフト</a></p>
<table border="1" cellpadding="6" cellspacing="0">
  <thead>
    <tr><th>日付</th><th>時間</th><th>募集</th><th>備考</th></tr>
  </thead>
  <tbody>
  <?php foreach ($shifts as $s): ?>
    <tr>
      <td><?= e($s['shift_date']) ?></td>
      <td><?= e(substr($s['start_time'],0,5).' - '.substr($s['end_time'],0,5)) ?></td>
      <td style="text-align:right"><?= (int)$s['slot_count'] ?></td>
      <td><?= e($s['note']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($shifts)): ?>
    <tr><td colspan="4">データがありません</td></tr>
  <?php endif; ?>
  </tbody>
</table>
