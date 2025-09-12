<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?> - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- ユーザー作成ページ専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/users-create.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>
</head>
<body>
    <!-- ヘッダー -->
    <div class="header">
        <h1><?php echo $title; ?></h1>
        <p class="subtitle"><?php echo $subtitle; ?></p>
    </div>
    
    <!-- アクションボタン -->
    <div class="action-buttons">
        <a href="<?php echo \Fuel\Core\Uri::create('users'); ?>" class="action-btn back">ユーザー一覧に戻る</a>
    </div>
    
    <!-- アラート表示 -->
    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <div class="form-card">
            <form id="user-form">
                <div class="form-group">
                    <label class="form-label" for="name">名前 *</label>
                    <input type="text" id="name" name="name" class="form-input" data-bind="value: name" placeholder="ユーザー名を入力" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="role">権限 *</label>
                    <select id="role" name="role" class="form-select" data-bind="value: role">
                        <option value="member">ユーザー</option>
                        <option value="admin">管理者</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="color">表示色</label>
                    <input type="color" id="color" name="color" class="form-input" data-bind="value: color" value="#3498db">
                    <span class="color-preview" data-bind="style: { backgroundColor: color }"></span>
                </div>
                
                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" id="is_active" name="is_active" data-bind="checked: isActive">
                        <label for="is_active">アクティブ状態</label>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn" data-bind="click: submitForm, disabled: loading">
                    <span data-bind="visible: !loading()">ユーザーを作成</span>
                    <span data-bind="visible: loading">作成中...</span>
                </button>
            </form>
        </div>
    </div>
    
    <!-- ローディング表示 -->
    <div data-bind="visible: loading" class="loading">
        読み込み中...
    </div>
    
    <!-- ナビゲーションヒント -->
    <div class="navigation-hint">
        ・必須項目を入力してユーザーを作成
    </div>
    
    <script>
        // Knockout.js ViewModel
        function UserCreateViewModel() {
            var self = this;
            
            // データ
            self.name = ko.observable('');
            self.role = ko.observable('member');
            self.color = ko.observable('#3498db');
            self.isActive = ko.observable(true);
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
            
            // フォーム送信
            self.submitForm = function() {
                // バリデーション
                if (!self.name()) {
                    self.showAlert('名前は必須です', 'error');
                    return;
                }
                
                if (!self.role()) {
                    self.showAlert('権限は必須です', 'error');
                    return;
                }
                
                if (!['member', 'admin'].includes(self.role())) {
                    self.showAlert('権限はmemberまたはadminである必要があります', 'error');
                    return;
                }
                
                self.loading(true);
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/users'); ?>',
                    type: 'POST',
                    data: {
                        name: self.name(),
                        role: self.role(),
                        color: self.color(),
                        is_active: self.isActive() ? 1 : 0,
                        csrf_token: 'dummy_token'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('ユーザーを作成しました', 'success');
                            // 作成後は一覧画面に戻る
                            setTimeout(function() {
                                window.location.href = '<?php echo \Fuel\Core\Uri::create('users'); ?>';
                            }, 1500);
                        } else {
                            self.showAlert('ユーザーの作成に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                            self.showAlert('ユーザーの作成に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        self.loading(false);
                    }
                });
            };
        }
        
        // ViewModelを適用
        ko.applyBindings(new UserCreateViewModel());
    </script>
</body>
</html>
