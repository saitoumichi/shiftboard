<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト作成 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- シフト作成ページ専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shifts-create.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>
</head>
<body>

<div class="create-page">
  <!-- ページ上部のヒーローバー（デザイン準拠） -->
  <div class="page-header">
    <div class="page-header-inner">
      <div class="page-title">シフトボード</div>
    </div>
  </div>

  <div class="form-container">
    <?php if (\Fuel\Core\Session::get_flash('success')): ?>
      <div class="alert alert-success"><?= e(\Fuel\Core\Session::get_flash('success')); ?></div>
    <?php endif; ?>
    <?php if (\Fuel\Core\Session::get_flash('error')): ?>
      <div class="alert alert-error"><?= e(\Fuel\Core\Session::get_flash('error')); ?></div>
    <?php endif; ?>

    <form id="shift-form" method="post" action="<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>">
      <!-- 上段：シフトタイトル／新規シフトタイトル -->
      <div class="top-row">
        <!-- <div class="form-group">
          <label for="shift_title">シフトタイトル/月</label>
          <input type="text" id="shift_title" name="shift_title"
            value="<?= e(\Fuel\Core\Input::post('shift_title', date('Y/m'))); ?>" placeholder="2025/09" required>
        </div> -->
        <div class="form-group">
          <label for="new_shift_title">新規シフトタイトル</label>
          <input type="text" id="new_shift_title" name="new_shift_title"
            value="<?= e(\Fuel\Core\Input::post('new_shift_title', ' ')); ?>" placeholder="平日シフト" required>
            
        </div>
      </div>

      <!-- 左ラベル／右入力の2カラム -->
      <div class="form-grid">
        <div class="form-line">
          <div class="form-label">日付</div>
          <div class="form-field">
            <input type="date" id="shift_date" name="shift_date" value="<?= e(\Fuel\Core\Input::post('shift_date', '')); ?>" required>
          </div>
        </div>
        <div class="form-line">
          <div class="form-label">開始時間</div>
          <div class="form-field">
            <input type="time" id="start_time" name="start_time" value="<?= e(\Fuel\Core\Input::post('start_time', '')); ?>" required>
          </div>
        </div>
        <div class="form-line">
          <div class="form-label">終了時間</div>
          <div class="form-field">
            <input type="time" id="end_time" name="end_time" value="<?= e(\Fuel\Core\Input::post('end_time', '')); ?>" required>
          </div>
        </div>
        <div class="form-line">
          <div class="form-label">定員数</div>
          <div class="form-field">
            <input type="number" id="slot_count" name="slot_count" min="1" value="<?= e(\Fuel\Core\Input::post('slot_count', '1')); ?>" required>
          </div>
        </div>
        <!-- <div class="form-line">
          <div class="form-label">備考</div>
          <div class="form-field">
            <textarea id="note" name="note" rows="4" placeholder="シフトに関する備考があれば入力してください"> -->
                <!-- <?= e(\Fuel\Core\Input::post('note', '')); ?> -->
            <!-- </textarea>
          </div>
        </div> -->
      </div>

      <div class="form-actions">
        <a class="btn btn-back" href="<?php echo \Fuel\Core\Uri::create('shifts'); ?>">戻る</a>
        <button class="btn btn-save" type="submit">保存</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
