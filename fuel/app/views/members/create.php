<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?> - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Uri::create('css/common.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Uri::create('js/common.js'); ?>"></script>
    
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
        
        .action-btn.back {
            background: #6c757d;
            color: white;
        }
        
        .action-btn.back:hover {
            background: #5a6268;
        }
        
        .main-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            box-sizing: border-box;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: inline-block;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .submit-btn {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .submit-btn:hover {
            background: #218838;
        }
        
        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
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
        <a href="<?php echo \Uri::create('members'); ?>" class="action-btn back">メンバー一覧に戻る</a>
    </div>
    
    <!-- アラート表示 -->
    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <div class="form-card">
            <form id="member-form">
                <div class="form-group">
                    <label class="form-label" for="name">名前 *</label>
                    <input type="text" id="name" name="name" class="form-input" data-bind="value: name" placeholder="メンバー名を入力" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="role">権限 *</label>
                    <select id="role" name="role" class="form-select" data-bind="value: role">
                        <option value="member">メンバー</option>
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
                    <span data-bind="visible: !loading()">メンバーを作成</span>
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
        ・必須項目を入力してメンバーを作成
    </div>
    
    <script>
        // Knockout.js ViewModel
        function MemberCreateViewModel() {
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
                    url: '<?php echo \Uri::create('api/members'); ?>',
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
                            self.showAlert('メンバーを作成しました', 'success');
                            // 作成後は一覧画面に戻る
                            setTimeout(function() {
                                window.location.href = '<?php echo \Uri::create('members'); ?>';
                            }, 1500);
                        } else {
                            self.showAlert('メンバーの作成に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('メンバーの作成に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        self.loading(false);
                    }
                });
            };
        }
        
        // ViewModelを適用
        ko.applyBindings(new MemberCreateViewModel());
    </script>
</body>
</html>
