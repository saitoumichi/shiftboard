<h2>シフト作成</h2>
<form action="/api/shifts/create" method="post">
  <?= \Form::csrf(); ?>">
  <p>
    <label>日付 <input type="date" name="shift_date" required></label>
    <label>開始 <input type="time" name="start_time" required></label>
    <label>終了 <input type="time" name="end_time" required></label>
    <label>募集 <input type="number" name="slot_count" min="1" value="1"></label>
  </p>
  <p><label>備考<br><textarea name="note" rows="3" cols="40"></textarea></label></p>
  <p>
    <button type="submit">登録</button>
    <a href="/shifts">戻る</a>
  </p>
</form>
