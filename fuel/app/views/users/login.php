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
            background: linear-gradient(135deg, #a8e6cf 0%, #dcedc8 50%, #f1f8e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* 葉っぱのアニメーション */
        .sakura-petals {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }
        
        .sakura-petal {
            position: absolute;
            font-size: 24px;
            opacity: 0.7;
            animation: fall linear infinite;
        }
        
        .sakura-petal:nth-child(1) { left: 10%; animation-duration: 15s; animation-delay: 0s; }
        .sakura-petal:nth-child(2) { left: 25%; animation-duration: 18s; animation-delay: 3s; font-size: 28px; }
        .sakura-petal:nth-child(3) { left: 40%; animation-duration: 20s; animation-delay: 6s; }
        .sakura-petal:nth-child(4) { left: 55%; animation-duration: 16s; animation-delay: 9s; font-size: 32px; }
        .sakura-petal:nth-child(5) { left: 70%; animation-duration: 19s; animation-delay: 12s; }
        .sakura-petal:nth-child(6) { left: 85%; animation-duration: 21s; animation-delay: 15s; font-size: 26px; }
        
        @keyframes fall {
            0% {
                top: -10%;
                transform: translateX(0) rotate(0deg);
            }
            25% {
                transform: translateX(40px) rotate(90deg);
            }
            50% {
                transform: translateX(-40px) rotate(180deg);
            }
            75% {
                transform: translateX(30px) rotate(270deg);
            }
            100% {
                top: 110%;
                transform: translateX(-30px) rotate(360deg);
            }
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(76, 175, 80, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            position: relative;
            z-index: 10;
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
            color: #1b5e20;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4caf50;
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
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
        }
        
        input[type="color"] {
            height: 50px;
            cursor: pointer;
        }
        
        .btn {
            background: linear-gradient(135deg, #66bb6a, #4caf50);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #4caf50, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .users-list {
            margin-top: 30px;
        }
        
        .users-list h2 {
            color: #1b5e20;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4caf50;
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
    <!-- 葉っぱのアニメーション -->
    <div class="sakura-petals">
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌿</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌱</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌿</div>
    </div>
    
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
                    <input type="color" id="color" name="color" value="#4caf50">
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
