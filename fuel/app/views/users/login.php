<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - Shiftboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h2 {
            color: #444;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="color"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="color"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        input[type="color"] {
            height: 50px;
            cursor: pointer;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s, transform 0.1s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .users-list {
            margin-top: 30px;
        }
        
        .users-list h2 {
            color: #444;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .user-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }
        
        .user-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .user-color {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 12px;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .edit-btn {
            background: #4CAF50;
            color: white;
        }
        
        .edit-btn:hover {
            background: #45a049;
        }
        
        .delete-btn {
            background: #f44336;
            color: white;
        }
        
        .delete-btn:hover {
            background: #da190b;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗓️ Shiftboard</h1>
        <p class="subtitle">シフト管理システム</p>
        
        <?php if (Session::get_flash('success')): ?>
            <div class="alert alert-success">
                <?php echo Session::get_flash('success'); ?>
            </div>
        <?php endif; ?>
        
        <?php if (Session::get_flash('error')): ?>
            <div class="alert alert-error">
                <?php echo Session::get_flash('error'); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <h2>新規ユーザー登録 / ログイン</h2>
            <form method="POST" action="<?php echo Uri::create('users/login'); ?>">
                <div class="form-group">
                    <label for="name">名前</label>
                    <input type="text" id="name" name="name" placeholder="あなたの名前を入力" required>
                </div>
                
                <div class="form-group">
                    <label for="color">カラー（識別用）</label>
                    <input type="color" id="color" name="color" value="#667eea">
                </div>
                
                <button type="submit" class="btn">ログイン / 登録</button>
            </form>
        </div>
        
        <?php if (!empty($users)): ?>
        <div class="users-list">
            <h2>既存のユーザーでログイン</h2>
            <?php foreach ($users as $u): ?>
            <div class="user-item">
                <div class="user-info" onclick="loginUser(<?php echo (int)$u['id']; ?>)">
                    <span class="user-color" style="background-color: <?php echo htmlspecialchars($u['color']); ?>;"></span>
                    <?php echo htmlspecialchars($u['name']); ?>
                </div>
                <div class="action-buttons">
                    <button class="edit-btn" onclick="event.stopPropagation(); editUser(<?php echo (int)$u['id']; ?>);" title="ユーザーを編集">
                        編集
                    </button>
                    <button class="delete-btn" onclick="event.stopPropagation(); deleteUser(<?php echo (int)$u['id']; ?>, '<?php echo htmlspecialchars($u['name']); ?>');" title="ユーザーを削除">
                        削除
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function loginUser(userId) {
            // 既存ユーザーとしてログイン
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo Uri::create('users/login'); ?>';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_id';
            input.value = userId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        
        function editUser(userId) {
            // ユーザー編集ページにリダイレクト
            window.location.href = '<?php echo Uri::create('users/edit'); ?>/' + userId;
        }
        
        function deleteUser(userId, userName) {
            if (confirm('本当に「' + userName + '」を削除しますか？')) {
                // ユーザー削除処理
                window.location.href = '<?php echo Uri::create('users/delete'); ?>/' + userId;
            }
        }
    </script>
</body>
</html>
