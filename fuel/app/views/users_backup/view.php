<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?> - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn.edit {
            background: #007bff;
            color: white;
        }
        
        .action-btn.edit:hover {
            background: #0056b3;
        }
        
        .action-btn.delete {
            background: #dc3545;
            color: white;
        }
        
        .action-btn.delete:hover {
            background: #c82333;
        }
        
        .action-btn.back {
            background: #6c757d;
            color: white;
        }
        
        .action-btn.back:hover {
            background: #5a6268;
        }
        
        .main-content {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            gap: 30px;
            padding: 0 20px;
        }
        
        .left-section {
            flex: 1;
        }
        
        .right-section {
            flex: 2;
        }
        
        .section-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-header {
            background: #34495e;
            color: white;
            padding: 10px 15px;
            margin: -20px -20px 20px -20px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 32px;
        }
        
        .user-info h2 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 24px;
        }
        
        .user-role {
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .user-role.admin {
            background: #dc3545;
            color: white;
        }
        
        .user-status {
            font-size: 14px;
            color: #6c757d;
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6c757d;
        }
        
        .shifts-list {
            margin-top: 20px;
        }
        
        .shift-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f8f9fa;
            transition: all 0.2s ease;
        }
        
        .shift-item:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
        
        .shift-date {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: bold;
            margin-right: 15px;
            min-width: 100px;
            text-align: center;
        }
        
        .shift-time {
            flex: 1;
            font-weight: 500;
            color: #333;
        }
        
        .shift-status {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .navigation-hint {
            position: fixed;
            bottom: 20px;
            left: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="header">
        <h1><?php echo $title; ?></h1>
        <p class="subtitle"><?php echo $subtitle; ?></p>
    </div>
    
    <!-- アクションボタン -->
    <div class="action-buttons">
        <button class="action-btn edit" data-bind="click: $root.editUser">編集</button>
        <button class="action-btn delete" data-bind="click: $root.deleteUser">削除</button>
        <a href="<?php echo \Fuel\Core\Uri::create('users'); ?>" class="action-btn back">ユーザー一覧に戻る</a>
    </div>
    
    <!-- アラート表示 -->
    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <!-- 左セクション -->
        <div class="left-section">
            <!-- ユーザー情報 -->
            <div class="section-card">
                <div class="section-header">ユーザー情報</div>
                
                <div class="user-profile">
                    <div class="user-avatar" data-bind="style: { backgroundColor: user().color || '#3498db' }">
                        <span data-bind="text: user().name ? user().name.charAt(0).toUpperCase() : '?'"></span>
                    </div>
                    <div class="user-info">
                        <h2 data-bind="text: user().name || '読み込み中...'"></h2>
                        <span class="user-role" data-bind="text: user().role === 'admin' ? '管理者' : 'ユーザー', css: { admin: user().role === 'admin' }"></span>
                        <div class="user-status">
                            ステータス: <span data-bind="text: user().is_active ? 'アクティブ' : '非アクティブ'"></span>
                        </div>
                    </div>
                </div>
                
                <div class="user-stats">
                    <div class="stat-card">
                        <div class="stat-number" data-bind="text: user().shift_count || 0"></div>
                        <div class="stat-label">参加シフト</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" data-bind="text: user().is_active ? '○' : '×'"></div>
                        <div class="stat-label">ステータス</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右セクション -->
        <div class="right-section">
            <!-- 参加シフト一覧 -->
            <div class="section-card">
                <div class="section-header">参加シフト一覧</div>
                
                <div class="shifts-list">
                    <!-- ko foreach: user().shifts -->
                    <div class="shift-item">
                        <div class="shift-date" data-bind="text: shift_date"></div>
                        <div class="shift-time" data-bind="text: start_time + ' - ' + end_time"></div>
                        <div class="shift-status" data-bind="text: status === 'confirmed' ? '確定' : '申請中'"></div>
                    </div>
                    <!-- /ko -->
                    
                    <!-- 参加シフトがない場合 -->
                    <div data-bind="visible: user().shifts && user().shifts.length === 0" class="empty-state">
                        <h3>参加シフトがありません</h3>
                        <p>このユーザーはまだシフトに参加していません</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ローディング表示 -->
    <div data-bind="visible: loading" class="loading">
        読み込み中...
    </div>
    
    <!-- ナビゲーションヒント -->
    <div class="navigation-hint">
        ・編集→ユーザー情報変更<br>
        ・削除→ユーザー削除
    </div>
    
    <script>
        // Knockout.js ViewModel
        function UserDetailViewModel() {
            var self = this;
            
            // データ
            self.user = ko.observable({});
            self.loading = ko.observable(false);
            
            // アラート表示
            self.showAlert = function(message, type) {
                var alert = document.getElementById('alert');
                var alertMessage = document.getElementById('alert-message');
                
                alert.className = 'alert alert-' + type;
                alertMessage.textContent = message;
                alert.style.display = 'block';
                
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 5000);
            };
            
            // ユーザー詳細を取得
            self.loadUserDetail = function() {
                self.loading(true);
                
                // URLからユーザーIDを取得
                var pathParts = window.location.pathname.split('/');
                var userId = pathParts[pathParts.length - 1];
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/users'); ?>/' + userId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.user(response.data);
                        } else {
                            self.showAlert('ユーザー詳細の取得に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('ユーザー詳細の取得に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        self.loading(false);
                    }
                });
            };
            
            // ユーザー編集
            self.editUser = function() {
                var user = self.user();
                if (!user) return;
                
                var newName = prompt('名前:', user.name);
                if (!newName) return;
                
                var newRole = prompt('権限 (member/admin):', user.role);
                if (!newRole || !['member', 'admin'].includes(newRole)) {
                    self.showAlert('権限はmemberまたはadminである必要があります', 'error');
                    return;
                }
                
                var newColor = prompt('色 (#RRGGBB):', user.color);
                if (!newColor) return;
                
                var newIsActive = confirm('アクティブ状態: ' + (user.is_active ? 'アクティブ' : '非アクティブ') + '\n\nアクティブにしますか？');
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/users'); ?>/' + user.id + '/update',
                    type: 'POST',
                    data: {
                        name: newName,
                        role: newRole,
                        color: newColor,
                        is_active: newIsActive ? 1 : 0,
                        csrf_token: 'dummy_token'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('ユーザーを更新しました', 'success');
                            self.loadUserDetail();
                        } else {
                            self.showAlert('ユーザーの更新に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('ユーザーの更新に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // ユーザー削除
            self.deleteUser = function() {
                var user = self.user();
                if (!user) return;
                
                if (!confirm('ユーザー「' + user.name + '」を削除しますか？\n\n注意: 参加シフトがある場合は削除できません。')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/users'); ?>/' + user.id + '/delete',
                    type: 'POST',
                    data: {
                        csrf_token: 'dummy_token'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('ユーザーを削除しました', 'success');
                            // 削除後は一覧画面に戻る
                            setTimeout(function() {
                                window.location.href = '<?php echo \Fuel\Core\Uri::create('users'); ?>';
                            }, 1500);
                        } else {
                            self.showAlert('ユーザーの削除に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('ユーザーの削除に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // 初期化
            self.loadUserDetail();
        }
        
        // ViewModelを適用
        ko.applyBindings(new UserDetailViewModel());
    </script>
</body>
</html>
