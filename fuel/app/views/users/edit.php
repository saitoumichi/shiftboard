<?php use Fuel\Core\Uri; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー情報編集 - シフトボード</title>
    <link rel="stylesheet" href="<?= \Fuel\Core\Uri::create('css/common.css') ?>">
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="color"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .form-group input[type="color"] {
            width: 80px;
            height: 50px;
            padding: 4px;
        }
        
        .color-preview {
            display: inline-block;
            margin-left: 10px;
            padding: 8px 16px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
        }
        
        .form-actions {
            margin-top: 30px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="header">
            <h1>ユーザー情報編集</h1>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= \Fuel\Core\Uri::create('users/update/' . $user->id) ?>">
            <div class="form-group">
                <label for="name">ユーザー名</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= htmlspecialchars($user->name) ?>" 
                       required
                       maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="color">色</label>
                <input type="color" 
                       id="color" 
                       name="color" 
                       value="<?= htmlspecialchars($user->color) ?>">
                <span class="color-preview" id="colorPreview" style="background-color: <?= htmlspecialchars($user->color) ?>">
                    プレビュー
                </span>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">更新</button>
                <a href="<?= \Fuel\Core\Uri::create('users/view/' . $user->id) ?>" class="btn btn-secondary">キャンセル</a>
            </div>
        </form>
    </div>
    
    <script>
        // 色のプレビューをリアルタイム更新
        document.getElementById('color').addEventListener('input', function() {
            const color = this.value;
            const preview = document.getElementById('colorPreview');
            preview.style.backgroundColor = color;
        });
    </script>
</body>
</html>

