<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>自分のシフト - ShiftBoard</title>
    
    <!-- Knockout.js -->
    <script src="<?php echo \Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
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
        
        .main-content {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            gap: 30px;
            padding: 0 20px;
        }
        
        .left-section {
            flex: 1;
            max-width: 300px;
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
        
        .period-specification {
            margin-bottom: 20px;
        }
        
        .period-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        
        .period-input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .period-controls {
            display: flex;
            gap: 10px;
        }
        
        .period-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #e1e8ed;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
        }
        
        .period-btn:hover {
            border-color: #3498db;
            background: #f8f9fa;
        }
        
        .navigation-bar {
            background: #34495e;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -20px -20px 20px -20px;
        }
        
        .screen-title {
            font-weight: bold;
        }
        
        .nav-buttons {
            display: flex;
            gap: 10px;
        }
        
        .nav-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back {
            background: #3498db;
            color: white;
        }
        
        .btn-back:hover {
            background: #2980b9;
        }
        
        .btn-csv {
            background: #27ae60;
            color: white;
            font-size: 12px;
            padding: 6px 12px;
        }
        
        .btn-csv:hover {
            background: #229954;
        }
        
        .shifts-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .shift-item {
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        
        .shift-item:hover {
            background: #e9ecef;
            border-color: #3498db;
        }
        
        .shift-date-time {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .shift-slot-info {
            font-size: 14px;
            color: #7f8c8d;
            background: #ecf0f1;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
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
        <h1>自分のシフト</h1>
        <p class="subtitle">自分のシフトを確認できる</p>
    </div>
    
    <!-- アラート表示 -->
    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <!-- 左セクション：期間指定 -->
        <div class="left-section">
            <div class="section-card">
                <div class="section-header">期間指定</div>
                
                <div class="period-specification">
                    <input type="text" class="period-input" id="period-input" placeholder="2025年11月" data-bind="value: periodInput">
                    
                    <div class="period-controls">
                        <button class="period-btn" data-bind="click: previousPeriod">‹</button>
                        <button class="period-btn" data-bind="click: nextPeriod">›</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右セクション：参加予定シフト一覧 -->
        <div class="right-section">
            <div class="section-card">
                <div class="navigation-bar">
                    <div class="screen-title" data-bind="text: screenTitle">参加予定シフト一覧</div>
                    <div class="nav-buttons">
                        <button class="nav-btn btn-back" data-bind="click: goBack">戻る</button>
                        <button class="nav-btn btn-csv" data-bind="click: exportCSV">CSV出力</button>
                    </div>
                </div>
                
                <!-- ローディング表示 -->
                <div data-bind="visible: loading" class="loading">
                    読み込み中...
                </div>
                
                <!-- シフト一覧 -->
                <div data-bind="visible: !loading() && myShifts().length > 0" class="shifts-list">
                    <div id="my-shifts-container">
                        <!-- JavaScriptで動的に生成 -->
                    </div>
                </div>
                
                <!-- シフトが無い場合 -->
                <div data-bind="visible: !loading() && myShifts().length === 0" class="empty-state">
                    <h3>参加予定のシフトがありません</h3>
                    <p>指定した期間に参加予定のシフトはありません。</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ナビゲーションヒント -->
    <div class="navigation-hint">
        ・戻る→シフト一覧
    </div>
    
    <script>
        // Knockout.js ViewModel
        function MyShiftsViewModel() {
            var self = this;
            
            // データ
            self.myShifts = ko.observableArray([]);
            self.loading = ko.observable(false);
            self.periodInput = ko.observable('');
            self.currentDate = ko.observable(new Date());
            self.screenTitle = ko.observable('参加予定シフト一覧');
            
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
            
            // 期間表示を更新
            self.updatePeriodDisplay = function() {
                var date = self.currentDate();
                var year = date.getFullYear();
                var month = date.getMonth() + 1;
                self.periodInput(year + '年' + month + '月');
            };
            
            // 前の期間
            self.previousPeriod = function() {
                var date = new Date(self.currentDate());
                date.setMonth(date.getMonth() - 1);
                self.currentDate(date);
                self.updatePeriodDisplay();
                self.loadMyShifts();
            };
            
            // 次の期間
            self.nextPeriod = function() {
                var date = new Date(self.currentDate());
                date.setMonth(date.getMonth() + 1);
                self.currentDate(date);
                self.updatePeriodDisplay();
                self.loadMyShifts();
            };
            
            // 自分のシフトを取得
            self.loadMyShifts = function() {
                self.loading(true);
                
                var date = self.currentDate();
                var year = date.getFullYear();
                var month = date.getMonth() + 1;
                
                // 月の開始日と終了日を計算
                var startDate = year + '-' + String(month).padStart(2, '0') + '-01';
                var endDate = new Date(year, month, 0).toISOString().split('T')[0];
                
                $.ajax({
                    url: '<?php echo \Uri::create('api/my/shifts'); ?>',
                    type: 'GET',
                    data: {
                        start: startDate,
                        end: endDate
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // データを整形
                            var formattedShifts = response.data.map(function(shift) {
                                return {
                                    id: shift.id,
                                    dateTime: shift.shift_date + ' ' + shift.start_time + '-' + shift.end_time,
                                    slotInfo: shift.assigned_count + '/' + shift.slot_count,
                                    shift_date: shift.shift_date,
                                    start_time: shift.start_time,
                                    end_time: shift.end_time,
                                    note: shift.note
                                };
                            });
                            self.myShifts(formattedShifts);
                            self.renderMyShifts(formattedShifts);
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
            
            // 自分のシフトをレンダリング
            self.renderMyShifts = function(shifts) {
                var container = document.getElementById('my-shifts-container');
                if (!container) return;
                
                container.innerHTML = '';
                
                shifts.forEach(function(shift) {
                    var itemElement = document.createElement('div');
                    itemElement.className = 'shift-item';
                    
                    var dateTimeDiv = document.createElement('div');
                    dateTimeDiv.className = 'shift-date-time';
                    dateTimeDiv.textContent = shift.dateTime;
                    itemElement.appendChild(dateTimeDiv);
                    
                    var slotInfoDiv = document.createElement('div');
                    slotInfoDiv.className = 'shift-slot-info';
                    slotInfoDiv.textContent = shift.slotInfo;
                    itemElement.appendChild(slotInfoDiv);
                    
                    container.appendChild(itemElement);
                });
            };
            
            // CSV出力
            self.exportCSV = function() {
                var shifts = self.myShifts();
                if (shifts.length === 0) {
                    self.showAlert('出力するシフトがありません', 'error');
                    return;
                }
                
                // CSVデータを生成
                var csvContent = '日付,開始時間,終了時間,参加者数/総枠数,備考\n';
                shifts.forEach(function(shift) {
                    csvContent += shift.shift_date + ',' + 
                                 shift.start_time + ',' + 
                                 shift.end_time + ',' + 
                                 shift.slotInfo + ',' + 
                                 (shift.note || '') + '\n';
                });
                
                // ファイルダウンロード
                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement('a');
                var url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'my_shifts_' + self.periodInput().replace(/[年月]/g, '') + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                self.showAlert('CSVファイルを出力しました', 'success');
            };
            
            // 戻る
            self.goBack = function() {
                window.location.href = '<?php echo \Uri::create('shifts'); ?>';
            };
            
            // 初期化
            self.updatePeriodDisplay();
            self.loadMyShifts();
        }
        
        // ViewModelを適用
        ko.applyBindings(new MyShiftsViewModel());
    </script>
</body>
</html>
