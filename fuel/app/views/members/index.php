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
        
        .action-btn.create {
            background: #28a745;
            color: white;
        }
        
        .action-btn.create:hover {
            background: #218838;
        }
        
        .action-btn.back {
            background: #6c757d;
            color: white;
        }
        
        .action-btn.back:hover {
            background: #5a6268;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .member-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .member-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .member-info h3 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        
        .member-role {
            background: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 5px;
            display: inline-block;
        }
        
        .member-role.admin {
            background: #dc3545;
            color: white;
        }
        
        .member-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 2px;
        }
        
        .member-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            flex: 1;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0056b3;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .btn-view {
            background: #6c757d;
            color: white;
        }
        
        .btn-view:hover {
            background: #5a6268;
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
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #495057;
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
    <!-- ナビゲーションバー -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?php echo \Fuel\Core\Uri::create('shifts'); ?>" class="navbar-brand">シフトボード</a>
        </div>
    </nav>

    <!-- ヘッダー -->
    <div class="header">
        <h1><?php echo $title; ?></h1>
        <p class="subtitle"><?php echo $subtitle; ?></p>
    </div>
    
    <!-- アクションボタン -->
    <div class="action-buttons">
        <a href="<?php echo \Fuel\Core\Uri::create('members/create'); ?>" class="action-btn create">新規メンバー作成</a>
        <a href="<?php echo \Fuel\Core\Uri::create('shifts'); ?>" class="action-btn back">シフト一覧に戻る</a>
    </div>
    
    <!-- アラート表示 -->
    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <!-- メンバー一覧 -->
        <div id="members-container" class="members-grid">
            <!-- メンバーカードがここに動的に生成される -->
        </div>
        
        <!-- 空の状態 -->
        <div id="empty-state" class="empty-state" style="display: none;">
            <h3>メンバーが登録されていません</h3>
            <p>新しいメンバーを作成してください</p>
        </div>
    </div>
    
    <!-- ローディング表示 -->
    <div data-bind="visible: loading" class="loading">
        読み込み中...
    </div>
    
    <!-- ナビゲーションヒント -->
    <div class="navigation-hint">
        ・新規作成→メンバー追加<br>
        ・編集→メンバー情報変更<br>
        ・削除→メンバー削除
    </div>
    
    <script>
        // Knockout.js ViewModel
        function MembersViewModel() {
            var self = this;
            
            // データ
            self.members = ko.observableArray([]);
            self.loading = ko.observable(false);
            
            // アラート表示（共通機能を使用）
            self.showAlert = function(message, type) {
                ShiftBoard.alert.show(message, type);
            };
            
            // メンバー一覧を取得
            self.loadMembers = function() {
                self.loading(true);
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/members'); ?>',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.members(response.data);
                            self.renderMembers(response.data);
                        } else {
                            self.showAlert('メンバー一覧の取得に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('メンバー一覧の取得に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        self.loading(false);
                    }
                });
            };
            
            // メンバーカードをレンダリング
            self.renderMembers = function(members) {
                var container = document.getElementById('members-container');
                var emptyState = document.getElementById('empty-state');
                
                if (!container) return;
                
                container.innerHTML = '';
                
                if (members.length === 0) {
                    if (emptyState) {
                        emptyState.style.display = 'block';
                    }
                    return;
                } else {
                    if (emptyState) {
                        emptyState.style.display = 'none';
                    }
                }
                
                members.forEach(function(member) {
                    var cardElement = document.createElement('div');
                    cardElement.className = 'member-card';
                    
                    // アバター
                    var avatarElement = document.createElement('div');
                    avatarElement.className = 'member-avatar';
                    avatarElement.style.backgroundColor = member.color || '#3498db';
                    avatarElement.textContent = member.name.charAt(0).toUpperCase();
                    
                    // ヘッダー
                    var headerElement = document.createElement('div');
                    headerElement.className = 'member-header';
                    headerElement.appendChild(avatarElement);
                    
                    var infoElement = document.createElement('div');
                    infoElement.className = 'member-info';
                    
                    var nameElement = document.createElement('h3');
                    nameElement.textContent = member.name;
                    infoElement.appendChild(nameElement);
                    
                    var roleElement = document.createElement('span');
                    roleElement.className = 'member-role ' + member.role;
                    roleElement.textContent = member.role === 'admin' ? '管理者' : 'メンバー';
                    infoElement.appendChild(roleElement);
                    
                    headerElement.appendChild(infoElement);
                    cardElement.appendChild(headerElement);
                    
                    // 統計情報
                    var statsElement = document.createElement('div');
                    statsElement.className = 'member-stats';
                    
                    var shiftStatElement = document.createElement('div');
                    shiftStatElement.className = 'stat-item';
                    shiftStatElement.innerHTML = '<div class="stat-number">' + member.shift_count + '</div><div class="stat-label">参加シフト</div>';
                    statsElement.appendChild(shiftStatElement);
                    
                    var statusStatElement = document.createElement('div');
                    statusStatElement.className = 'stat-item';
                    statusStatElement.innerHTML = '<div class="stat-number">' + (member.is_active ? '○' : '×') + '</div><div class="stat-label">ステータス</div>';
                    statsElement.appendChild(statusStatElement);
                    
                    cardElement.appendChild(statsElement);
                    
                    // アクションボタン
                    var actionsElement = document.createElement('div');
                    actionsElement.className = 'member-actions';
                    
                    var viewBtn = document.createElement('button');
                    viewBtn.className = 'btn btn-view';
                    viewBtn.textContent = '詳細';
                    viewBtn.addEventListener('click', function() {
                        self.viewMember(member);
                    });
                    actionsElement.appendChild(viewBtn);
                    
                    var editBtn = document.createElement('button');
                    editBtn.className = 'btn btn-edit';
                    editBtn.textContent = '編集';
                    editBtn.addEventListener('click', function() {
                        self.editMember(member);
                    });
                    actionsElement.appendChild(editBtn);
                    
                    var deleteBtn = document.createElement('button');
                    deleteBtn.className = 'btn btn-delete';
                    deleteBtn.textContent = '削除';
                    deleteBtn.addEventListener('click', function() {
                        self.deleteMember(member);
                    });
                    actionsElement.appendChild(deleteBtn);
                    
                    cardElement.appendChild(actionsElement);
                    container.appendChild(cardElement);
                });
            };
            
            // メンバー詳細表示
            self.viewMember = function(member) {
                window.location.href = '<?php echo \Fuel\Core\Uri::create('members'); ?>/' + member.id;
            };
            
            // メンバー編集
            self.editMember = function(member) {
                var newName = prompt('名前:', member.name);
                if (!newName) return;
                
                var newRole = prompt('権限 (member/admin):', member.role);
                if (!newRole || !['member', 'admin'].includes(newRole)) {
                    self.showAlert('権限はmemberまたはadminである必要があります', 'error');
                    return;
                }
                
                var newColor = prompt('色 (#RRGGBB):', member.color);
                if (!newColor) return;
                
                var newIsActive = confirm('アクティブ状態: ' + (member.is_active ? 'アクティブ' : '非アクティブ') + '\n\nアクティブにしますか？');
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/members'); ?>/' + member.id + '/update',
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
                            self.showAlert('メンバーを更新しました', 'success');
                            self.loadMembers();
                        } else {
                            self.showAlert('メンバーの更新に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('メンバーの更新に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // メンバー削除
            self.deleteMember = function(member) {
                if (!confirm('メンバー「' + member.name + '」を削除しますか？\n\n注意: 参加シフトがある場合は削除できません。')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/members'); ?>/' + member.id + '/delete',
                    type: 'POST',
                    data: {
                        csrf_token: 'dummy_token'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('メンバーを削除しました', 'success');
                            self.loadMembers();
                        } else {
                            self.showAlert('メンバーの削除に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('メンバーの削除に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // 初期化
            self.loadMembers();
        }
        
        // ViewModelを適用
        ko.applyBindings(new MembersViewModel());
    </script>
</body>
</html>
