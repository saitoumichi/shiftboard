<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト作成 - ShiftBoard</title>
    
    <!-- Knockout.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-min.js"></script>
    
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #2c3e50;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 20px;
            text-align: left;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #bdc3c7;
        }
        
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            margin-top: 100px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-back {
            background: #3498db;
            color: white;
        }
        
        .btn-back:hover {
            background: #2980b9;
        }
        
        .btn-save {
            background: #e74c3c;
            color: white;
        }
        
        .btn-save:hover {
            background: #c0392b;
        }
        
        .btn-save:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
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
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
            display: none;
        }
        
        .navigation-hint {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: #bdc3c7;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="header">
        <h1>シフト作成</h1>
        <p class="subtitle">- シフトを作成できる</p>
    </div>
    
    <!-- フォームコンテナ -->
    <div class="form-container">
        <!-- アラート表示 -->
        <div id="alert" class="alert">
            <span id="alert-message"></span>
        </div>
        
        <!-- ローディング表示 -->
        <div id="loading" class="loading">
            保存中...
        </div>
        
        <form id="shift-form">
            <!-- シフトタイトル/月 -->
            <div class="form-group">
                <label for="shift_title">シフトタイトル/月</label>
                <input type="text" id="shift_title" name="shift_title" placeholder="2025/09" required>
            </div>
            
            <!-- 新規シフトタイトル -->
            <div class="form-group">
                <label for="new_shift_title">新規シフトタイトル</label>
                <input type="text" id="new_shift_title" name="new_shift_title" placeholder="平日シフト" required>
            </div>
            
            <!-- 日付・時間・定員 -->
            <div class="form-row">
                <div class="form-group">
                    <label for="shift_date">日付</label>
                    <input type="date" id="shift_date" name="shift_date" required>
                </div>
                <div class="form-group">
                    <label for="start_time">開始時間</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="end_time">終了時間</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>
                <div class="form-group">
                    <label for="slot_count">定員数</label>
                    <input type="number" id="slot_count" name="slot_count" min="1" value="1" required>
                </div>
            </div>
            
            <!-- 備考 -->
            <div class="form-group">
                <label for="note">備考</label>
                <textarea id="note" name="note" rows="4" placeholder="シフトに関する備考があれば入力してください"></textarea>
            </div>
            
            <!-- アクションボタン -->
            <div class="form-actions">
                <button type="button" class="btn btn-back" onclick="goBack()">戻る</button>
                <button type="submit" class="btn btn-save" id="save-btn">保存</button>
            </div>
        </form>
    </div>
    
    <!-- ナビゲーションヒント -->
    <div class="navigation-hint">
        ・戻る→シフト一覧
    </div>
    
    <script>
        // Knockout.js ViewModel
        function ShiftCreateViewModel() {
            var self = this;
            
            // フォームデータ
            self.shiftTitle = ko.observable('');
            self.newShiftTitle = ko.observable('');
            self.shiftDate = ko.observable('');
            self.startTime = ko.observable('');
            self.endTime = ko.observable('');
            self.slotCount = ko.observable(1);
            self.note = ko.observable('');
            
            // 状態管理
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
                if (!self.shiftDate() || !self.startTime() || !self.endTime()) {
                    self.showAlert('必須項目を入力してください', 'error');
                    return;
                }
                
                if (self.startTime() >= self.endTime()) {
                    self.showAlert('終了時間は開始時間より後にしてください', 'error');
                    return;
                }
                
                self.loading(true);
                document.getElementById('save-btn').disabled = true;
                
                // AJAX送信
                $.ajax({
                    url: '<?php echo \Uri::create('api/shifts'); ?>',
                    type: 'POST',
                    data: {
                        shift_date: self.shiftDate(),
                        start_time: self.startTime(),
                        end_time: self.endTime(),
                        slot_count: self.slotCount(),
                        note: self.note(),
                        csrf_token: 'dummy_token' // 簡易実装
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('シフトが作成されました', 'success');
                            setTimeout(function() {
                                window.location.href = '<?php echo \Uri::create('shifts'); ?>';
                            }, 1500);
                        } else {
                            self.showAlert('シフトの作成に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフトの作成に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        self.loading(false);
                        document.getElementById('save-btn').disabled = false;
                    }
                });
            };
        }
        
        // ViewModelを適用
        var viewModel = new ShiftCreateViewModel();
        ko.applyBindings(viewModel);
        
        // フォーム送信イベント
        document.getElementById('shift-form').addEventListener('submit', function(e) {
            e.preventDefault();
            viewModel.submitForm();
        });
        
        // 戻るボタン
        function goBack() {
            window.location.href = '<?php echo \Uri::create('shifts'); ?>';
        }
        
        // ローディング表示制御
        viewModel.loading.subscribe(function(loading) {
            document.getElementById('loading').style.display = loading ? 'block' : 'none';
        });
    </script>
</body>
</html>
