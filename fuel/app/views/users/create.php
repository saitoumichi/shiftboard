<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録・選択</title>
    
    <!-- ベースURL設定 -->
    <script>window.APP_BASE="<?php echo \Fuel\Core\Uri::base(false); ?>";</script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 2rem; }
        .container { max-width: 800px; margin: auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e9ecef; padding-bottom: 1rem; margin-bottom: 2rem; }
        .header h1 { margin: 0; color: #333; font-size: 1.5rem; }
        .main-content { margin-top: 2rem; }
        .page-title { text-align: center; color: #007bff; margin-bottom: 2rem; font-size: 2rem; }
        .users-section { margin-bottom: 3rem; }
        .section-title { font-size: 1.25rem; color: #333; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #007bff; }
        .users-list { list-style: none; padding: 0; display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; }
        .user-item {
            width: 150px;
            padding: 1rem;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .user-item:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .user-avatar { width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; color: white; }
        .user-name { font-weight: 500; color: #333; margin-bottom: 0.25rem; }
        .user-color { font-size: 0.875rem; color: #666; }
        .form-section { background: #f8f9fa; padding: 2rem; border-radius: 8px; border: 1px solid #e9ecef; }
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; }
        .form-input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .form-input:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 2px rgba(0,123,255,0.25); }
        .btn { background: #007bff; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; font-size: 1rem; transition: background 0.2s ease; }
        .btn:hover { background: #0056b3; }
        .btn:disabled { background: #6c757d; cursor: not-allowed; }
        .error { color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; }
        .success { color: #28a745; font-size: 0.875rem; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>ShiftBoard</h1>
        </header>

        <main class="main-content">
            <h2 class="page-title">ユーザー登録・選択</h2>

            <!-- 既存ユーザー一覧 -->
            <section class="users-section">
                <h3 class="section-title">既存ユーザーを選択</h3>
                <ul class="users-list" id="users-list">
                    <!-- JavaScriptで動的に生成 -->
                </ul>
            </section>

            <!-- 新規ユーザー登録 -->
            <section class="form-section">
                <h3 class="section-title">新規ユーザー登録</h3>
                <form id="user-form">
                    <div class="form-group">
                        <label for="name" class="form-label">名前</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                        <div id="name-error" class="error"></div>
                    </div>
                    <div class="form-group">
                        <label for="color" class="form-label">色</label>
                        <input type="color" id="color" name="color" class="form-input" value="#007bff">
                        <div id="color-error" class="error"></div>
                    </div>
                    <button type="submit" class="btn" id="submit-btn">登録</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        // ユーザー一覧を読み込み
        function loadUsers() {
            fetch(`${window.API}/users`)
                .then(response => response.json())
                .then(data => {
                    const usersList = document.getElementById('users-list');
                    usersList.innerHTML = '';
                    
                    if (data.success && data.data) {
                        data.data.forEach(user => {
                            const li = document.createElement('li');
                            li.className = 'user-item';
                            li.onclick = () => selectUser(user.id);
                            
                            li.innerHTML = `
                                <div class="user-avatar" style="background-color: ${user.color}">
                                    ${user.name.charAt(0).toUpperCase()}
                                </div>
                                <div class="user-name">${user.name}</div>
                                <div class="user-color">${user.color}</div>
                            `;
                            usersList.appendChild(li);
                        });
                    }
                })
                .catch(error => console.error('Error loading users:', error));
        }

        // ユーザー選択
        function selectUser(userId) {
            // セッションにユーザーIDを保存（簡易実装）
            sessionStorage.setItem('user_id', userId);
            window.location.href = '/shifts';
        }

        // フォーム送信
        document.getElementById('user-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('name').value.trim(),
                color: document.getElementById('color').value
            };

            // バリデーション
            if (!formData.name) {
                document.getElementById('name-error').textContent = '名前を入力してください';
                return;
            }

            // 送信
            fetch(`${window.API}/users`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 登録成功
                    sessionStorage.setItem('user_id', data.data.id);
                    window.location.href = '/shifts';
                } else {
                    // エラー表示
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const errorElement = document.getElementById(field + '-error');
                            if (errorElement) {
                                errorElement.textContent = data.errors[field];
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('登録に失敗しました');
            });
        });

        // ページ読み込み時にユーザー一覧を取得
        loadUsers();
    </script>
</body>
</html>