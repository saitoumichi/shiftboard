<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト一覧 - ShiftBoard</title>
    
    <!-- Knockout.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-min.js"></script>
    
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }
        .shifts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .shift-card {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .shift-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .shift-date {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .shift-time {
            color: #666;
            margin-bottom: 10px;
        }
        .shift-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .slot-count {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .shift-note {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .shift-actions {
            display: flex;
            gap: 10px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
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
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .assigned-users {
            margin-top: 10px;
        }
        .user-tag {
            display: inline-block;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 12px;
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>シフト一覧</h1>
            <a href="<?php echo \Uri::create('shifts/create'); ?>" class="btn">新しいシフトを作成</a>
        </div>
        
        <!-- アラート表示 -->
        <div data-bind="visible: alertMessage" class="alert" data-bind="css: { 'alert-success': alertType() === 'success', 'alert-error': alertType() === 'error' }">
            <span data-bind="text: alertMessage"></span>
        </div>
        
        <!-- ローディング表示 -->
        <div data-bind="visible: loading" class="loading">
            読み込み中...
        </div>
        
        <!-- シフト一覧 -->
        <div data-bind="visible: !loading() && shifts().length > 0" class="shifts-grid">
            <!-- ko foreach: shifts -->
            <div class="shift-card">
                <div class="shift-date" data-bind="text: shift_date"></div>
                <div class="shift-time" data-bind="text: start_time + ' - ' + end_time"></div>
                
                <div class="shift-info">
                    <span class="slot-count">募集: <span data-bind="text: slot_count"></span>名</span>
                    <span class="slot-count">空き: <span data-bind="text: available_slots"></span>名</span>
                </div>
                
                <!-- ko if: note -->
                <div class="shift-note" data-bind="text: note"></div>
                <!-- /ko -->
                
                <!-- 割り当て済みユーザー -->
                <div data-bind="visible: assigned_users.length > 0" class="assigned-users">
                    <strong>参加者:</strong>
                    <!-- ko foreach: assigned_users -->
                    <span class="user-tag" data-bind="text: name + ' (' + role + ')'"></span>
                    <!-- /ko -->
                </div>
                
                <div class="shift-actions">
                    <button class="btn btn-sm" data-bind="click: $parent.viewShift">詳細</button>
                    <button class="btn btn-sm" data-bind="click: $parent.editShift">編集</button>
                    <button class="btn btn-danger btn-sm" data-bind="click: $parent.deleteShift">削除</button>
                </div>
            </div>
            <!-- /ko -->
        </div>
        
        <!-- シフトが無い場合 -->
        <div data-bind="visible: !loading() && shifts().length === 0">
            <p>シフトが登録されていません。</p>
        </div>
    </div>
    
    <script>
        // Knockout.js ViewModel
        function ShiftViewModel() {
            var self = this;
            
            // データ
            self.shifts = ko.observableArray([]);
            self.loading = ko.observable(false);
            self.alertMessage = ko.observable('');
            self.alertType = ko.observable('');
            
            // アラート表示
            self.showAlert = function(message, type) {
                self.alertMessage(message);
                self.alertType(type);
                setTimeout(function() {
                    self.alertMessage('');
                }, 5000);
            };
            
            // シフト一覧を取得
            self.loadShifts = function() {
                self.loading(true);
                
                $.ajax({
                    url: '<?php echo \Uri::create('api/shifts'); ?>',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.shifts(response.data);
                        } else {
                            self.showAlert('シフト一覧の取得に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフト一覧の取得に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        self.loading(false);
                    }
                });
            };
            
            // シフト詳細表示
            self.viewShift = function(shift) {
                window.location.href = '<?php echo \Uri::create('shifts'); ?>/' + shift.id;
            };
            
            // シフト編集
            self.editShift = function(shift) {
                window.location.href = '<?php echo \Uri::create('shifts'); ?>/' + shift.id + '/edit';
            };
            
            // シフト削除
            self.deleteShift = function(shift) {
                if (!confirm('このシフトを削除しますか？')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo \Uri::create('api/shifts'); ?>/' + shift.id + '/delete',
                    type: 'POST',
                    data: {
                        csrf_token: 'dummy_token' // 簡易実装
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('シフトが削除されました', 'success');
                            self.loadShifts(); // 一覧を再読み込み
                        } else {
                            self.showAlert('シフトの削除に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフトの削除に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // 初期化
            self.loadShifts();
        }
        
        // ViewModelを適用
        ko.applyBindings(new ShiftViewModel());
    </script>
</body>
</html>