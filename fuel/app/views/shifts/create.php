<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト作成 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- シフト作成ページ専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shifts-create.css'); ?>">
    
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
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="navbar-brand">シフトボード</div>
                    <div class="navbar-title">シフト作成</div>
                </div>
            </div>
        </div>
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
                <input type="text" id="shift_title" name="shift_title" placeholder="2025/09" data-bind="value: shiftTitle" required>
            </div>
            
            <!-- 新規シフトタイトル -->
            <div class="form-group">
                <label for="new_shift_title">新規シフトタイトル</label>
                <input type="text" id="new_shift_title" name="new_shift_title" placeholder="平日シフト" data-bind="value: newShiftTitle" required>
            </div>
            
            <div class="form-grid">
                <div class="form-line">
                    <div class="form-label">日付</div>
                    <div class="form-field">
                        <input type="date" id="shift_date" name="shift_date" data-bind="value: shiftDate" required>
                    </div>
                </div>
                <div class="form-line">
                    <div class="form-label">開始時間</div>
                    <div class="form-field">
                        <input type="time" id="start_time" name="start_time" data-bind="value: startTime" required>
                    </div>
                </div>
                <div class="form-line">
                    <div class="form-label">終了時間</div>
                    <div class="form-field">
                        <input type="time" id="end_time" name="end_time" data-bind="value: endTime" required>
                    </div>
                </div>
                <div class="form-line">
                    <div class="form-label">定員数</div>
                    <div class="form-field">
                        <input type="number" id="slot_count" name="slot_count" min="1" value="1" data-bind="value: slotCount" required>
                    </div>
                </div>
                <div class="form-line">
                    <div class="form-label">備考</div>
                    <div class="form-field">
                        <textarea id="note" name="note" rows="4" placeholder="シフトに関する備考があれば入力してください" data-bind="value: note"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- アクションボタン -->
            <div class="form-actions">
                <button type="button" class="btn btn-back" onclick="goBack()">戻る</button>
                <button type="submit" class="btn btn-save" id="save-btn" data-bind="click: $root.submitForm">保存</button>
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
                // デバッグ用：現在の値をコンソールに出力
                console.log('Debug - Form values:');
                console.log('shiftDate:', self.shiftDate());
                console.log('startTime:', self.startTime());
                console.log('endTime:', self.endTime());
                console.log('slotCount:', self.slotCount());
                console.log('note:', self.note());
                
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
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>',
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
                                window.location.href = '<?php echo \Fuel\Core\Uri::create('shifts'); ?>';
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
            window.location.href = '<?php echo \Fuel\Core\Uri::create('shifts'); ?>';
        }
        
        // ローディング表示制御
        viewModel.loading.subscribe(function(loading) {
            document.getElementById('loading').style.display = loading ? 'block' : 'none';
        });
    </script>
</body>
</html>
