<?php
// 変数の安全な初期化
if (!isset($users)) {
    $users = array();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>ユーザー登録</title>
  
  <!-- ユーザーIDとAPIベースURL設定 -->
  <meta name="current-user-id" content="0">
  <meta name="api-base" content="/api">
  <script>
    window.API_BASE = '/api';
    window.CURRENT_USER_ID = 0;  // ログインページでは常に0
  </script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background-color: #f5f5f5;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .container {
      max-width: 600px;
      margin: 40px auto;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 0;
      overflow: hidden;
    }
    
    .header {
      background-color: #2d3748;
      color: white;
      padding: 20px 30px;
      font-weight: bold;
      font-size: 24px;
      margin: 0;
    }
    
    .content {
      padding: 40px;
    }
    
    .title {
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      color: #333;
      margin-top: 20px;
      margin-bottom: 30px;
    }
    
    .form-group {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      position: relative;
      width: 100%;
    }
    
    .form-group label {
      min-width: 120px;
      color: #333;
      font-weight: 500;
      margin: 0;
      margin-right: 30px;
      text-align: right;
    }
    
    .form-group input {
      flex: 1;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
    }
    
    .form-group input[type="color"] {
      width: 40px;
      height: 40px;
      padding: 0;
      border: 1px solid #ddd;
      border-radius: 4px;
      cursor: pointer;
      flex: none;
    }
    
    .color-input-container {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      width: 100%;
    }
    
    .color-input-wrapper {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      padding: 4px;
      border: 1px solid #ddd;
      border-radius: 4px;
      background-color: white;
      position: relative;
    }
    
    .register-btn {
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 16px;
      background-color:rgb(19, 82, 149);
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      box-shadow: 0 2px 4px rgba(37, 104, 174, 0.3);
      transition: background-color 0.2s;
    }
    
    .register-btn:hover {
      background-color:rgb(16, 68, 123);
    }
    
    .existing-users {
      margin-top: 80px;
    }
    
    .existing-users h3 {
      color: #333;
      margin-bottom: 15px;
      font-size: 18px;
    }
    
    .user-item {
      padding: 8px 0;
      color: #333;
      cursor: pointer;
      font-size: 16px;
      line-height: 1.4;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #eee;
    }
    
    .user-item:last-child {
      border-bottom: none;
    }
    
    .user-info {
      display: flex;
      align-items: center;
      flex: 1;
    }
    
    .user-color {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 8px;
      display: inline-block;
    }
    
    .user-item:hover {
      color: #007bff;
    }
    
    .action-buttons {
      display: flex;
      gap: 8px;
      margin-left: 10px;
    }
    
    .edit-btn {
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 12px;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .edit-btn:hover {
      background-color: #0056b3;
    }
    
    .delete-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 12px;
      cursor: pointer;
      opacity: 1;
      transition: background-color 0.2s;
    }
    
    .delete-btn:hover {
      background-color: #c82333;
    }
    
    
    .error {
      color: #dc3545;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    
    .login-form {
      margin-top: 30px;
      padding-top: 30px;
      border-top: 1px solid #eee;
    }
    
    .login-form h3 {
      color: #333;
      margin-bottom: 15px;
      font-size: 18px;
    }
    
    .login-form select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
      margin-bottom: 15px;
    }
    
    .login-btn {
      background-color: #28a745;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      box-shadow: 0 2px 4px rgba(40,167,69,0.3);
      transition: background-color 0.2s;
    }
    
    .login-btn:hover {
      background-color: #218838;
    }
    
    .no-users-message {
      text-align: center;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 8px;
      margin: 20px 0;
    }
    
    .no-users-message p {
      margin-bottom: 15px;
      color: #666;
      font-size: 16px;
    }
    
    .create-test-users-btn {
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 10px 20px;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .create-test-users-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  
  <div class="container">
    <div class="header">
      LOGO
    </div>
    
    <div class="content">
      <h1 class="title">ユーザー登録</h1>
      
      <?php if (!empty($error)): ?>
        <div class="error"><?= e($error) ?></div>
      <?php endif; ?>
      
      <form method="post" action="/users/create" onsubmit="console.log('フォーム送信開始！'); return true;">
        <div class="form-group">
          <label for="name">ユーザー名</label>
          <input type="text" id="name" name="name" autocomplete="username" required onchange="console.log('ユーザー名変更:', this.value);">
        </div>
        
        <div class="form-group">
          <label for="color">色</label>
          <div class="color-input-container">
            <div class="color-input-wrapper">
              <input type="color" id="color" name="color" value="#000000" autocomplete="off" onchange="console.log('色変更:', this.value); updateColorPreview();">
              <div class="color-preview" id="color-preview"></div>
            </div>
            <button type="submit" class="register-btn" onclick="console.log('登録ボタンクリック！');">登録</button>
          </div>
        </div>
      </form>
      
      <div class="existing-users">
        <h3>既存のユーザー</h3>
        
        <div class="user-list">
          <?php if (is_array($users) && !empty($users)): ?>
            <?php foreach ($users as $u): ?>
              <div class="user-item">
                <div class="user-info" onclick="loginUser(<?= (int)$u->id ?>)">
                  <span class="user-color" style="background-color: <?= e($u->color) ?>;"></span>
                  <?= e($u->name) ?>
                </div>
                <div class="action-buttons">
                  <button class="edit-btn" onclick="event.stopPropagation(); editUser(<?= (int)$u->id ?>);" title="ユーザーを編集">
                    編集
                  </button>
                  <button class="delete-btn" onclick="event.stopPropagation(); deleteUser(<?= (int)$u->id ?>, '<?= e($u->name) ?>');" title="ユーザーを削除">
                    削除
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="user-item">
              ユーザーが存在しません
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
  
  <script>
    function updateColorPreview() {
      const colorInput = document.getElementById('color');
      const colorPreview = document.getElementById('color-preview');
      colorPreview.style.backgroundColor = colorInput.value;
    }
    
    function loginUser(userId) {
      console.log('loginUser関数実行:', userId);
      // ログインAPIを呼び出し
      window.location.href = '/users/login?user_id=' + userId;
    }
    
    function editUser(userId) {
      console.log('editUser関数実行:', userId);
      // 編集ページにリダイレクト
      window.location.href = '/users/edit/' + userId;
    }
    
    function deleteUser(userId, userName) {
      console.log('deleteUser関数実行:', userId, userName);
      if (confirm('ユーザー「' + userName + '」を削除しますか？\n\n注意：このユーザーが作成したシフトと参加しているシフトもすべて削除されます。\nこの操作は取り消せません。')) {
        // 削除APIを呼び出し
        window.location.href = '/users/delete/' + userId;
      }
    }
    
    function createTestUsers() {
      console.log('createTestUsers関数実行');
      if (confirm('テストユーザーを作成しますか？\n\n以下のユーザーが作成されます：\n- 田中太郎\n- 佐藤花子\n- 鈴木一郎')) {
        // テストユーザー作成APIを呼び出し
        fetch('/users/create_test_users', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.ok) {
            alert('テストユーザーを作成しました！');
            location.reload();
          } else {
            alert('エラー: ' + (data.message || 'テストユーザーの作成に失敗しました'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('エラー: テストユーザーの作成に失敗しました');
        });
      }
    }
    
    // 初期化
    updateColorPreview();
  </script>
  
</body>
</html>