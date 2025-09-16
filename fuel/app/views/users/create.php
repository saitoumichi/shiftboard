<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .header {
            background-color: #333;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .main-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .page-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-section {
            margin-bottom: 3rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-row {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-row label {
            min-width: 80px;
            margin-bottom: 0;
        }

        .form-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        .color-preview {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f0f0f0;
            cursor: pointer;
        }

        .register-btn {
            background-color: #007bff;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .register-btn:hover {
            background-color: #0056b3;
        }

        .users-section {
            margin-top: 3rem;
        }

        .section-title {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #007bff;
        }

        .users-list {
            list-style: none;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }

        .user-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 1rem;
            border: 1px solid #ddd;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            display: none;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LOGO</h1>
    </div>

    <div class="container">
        <div class="main-content">
            <h1 class="page-title">ユーザー登録</h1>

            <div id="success-alert" class="alert alert-success"></div>
            <div id="error-alert" class="alert alert-error"></div>

            <div class="form-section">
                <form id="user-form">
                    <div class="form-group">
                        <div class="form-row">
                            <label for="user_name">ユーザー名</label>
                            <input type="text" id="user_name" name="name" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-row">
                            <label for="user_color">色</label>
                            <input type="color" id="user_color" name="color" class="form-input" value="#007bff">
                            <div class="color-preview" id="color-preview"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="register-btn">登録</button>
                    </div>
                </form>
            </div>

            <div class="users-section">
                <h2 class="section-title">既存のユーザー</h2>
                <ul class="users-list" id="users-list">
                    <!-- ユーザーリストはJavaScriptで動的に生成 -->
                </ul>
            </div>

            <a href="/shifts" class="back-link">← シフト一覧に戻る</a>
        </div>
    </div>

    <script>
        // カラーピッカーの変更をプレビューに反映
        document.getElementById('user_color').addEventListener('input', function() {
            document.getElementById('color-preview').style.backgroundColor = this.value;
        });

        // 初期カラープレビューを設定
        document.getElementById('color-preview').style.backgroundColor = document.getElementById('user_color').value;

        // フォーム送信処理
        document.getElementById('user-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = {
                name: document.getElementById('user_name').value.trim(),
                color: document.getElementById('user_color').value
            };

            // バリデーション
            if (!formData.name) {
                showAlert('ユーザー名を入力してください。', 'error');
                return;
            }

            // AJAX送信
            fetch('/api/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok || data.success) {
                    showAlert('ユーザーが正常に登録されました。', 'success');
                    document.getElementById('user-form').reset();
                    document.getElementById('color-preview').style.backgroundColor = '#007bff';
                    loadUsers(); // ユーザーリストを再読み込み
                } else {
                    const errorMsg = data.message || data.error || '不明なエラー';
                    showAlert('ユーザーの登録に失敗しました: ' + errorMsg, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('ユーザーの登録に失敗しました。', 'error');
            });
        });

        // アラート表示関数
        function showAlert(message, type) {
            const alertId = type === 'success' ? 'success-alert' : 'error-alert';
            const alert = document.getElementById(alertId);
            alert.textContent = message;
            alert.style.display = 'block';

            // 3秒後に自動で非表示
            setTimeout(function() {
                alert.style.display = 'none';
            }, 3000);
        }

        // ユーザーリスト読み込み
        function loadUsers() {
            fetch('/api/users')
            .then(response => response.json())
            .then(data => {
                if (data.ok || data.success) {
                    const usersList = document.getElementById('users-list');
                    usersList.innerHTML = '';

                    if (data.users && data.users.length > 0) {
                        data.users.forEach(user => {
                            const li = document.createElement('li');
                            li.className = 'user-item';
                            li.innerHTML = `
                                <div class="user-color" style="background-color: ${user.color || '#007bff'}"></div>
                                <div class="user-name">${user.name}</div>
                            `;
                            usersList.appendChild(li);
                        });
                    } else {
                        usersList.innerHTML = '<li>ユーザーが登録されていません。</li>';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
            });
        }

        // ページ読み込み時にユーザーリストを読み込み
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });
    </script>
</body>
</html>