<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト一覧 - ShiftBoard</title>
    
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .view-toggle {
            display: flex;
            gap: 10px;
        }
        
        .view-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .view-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .main-content {
            display: flex;
            max-width: 1400px;
            margin: 20px auto;
            gap: 20px;
            padding: 0 20px;
        }
        
        .calendar-section {
            flex: 2;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .recruitment-section {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .month-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nav-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
        }
        
        .current-month {
            font-size: 18px;
            font-weight: bold;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #ddd;
            border: 1px solid #ddd;
        }
        
        .calendar-day {
            background: white;
            padding: 10px;
            min-height: 80px;
            position: relative;
        }
        
        .calendar-day.weekend {
            background: #f8f9fa;
        }
        
        .day-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .shift-block {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 3px;
            padding: 2px 4px;
            margin: 1px 0;
            font-size: 11px;
            cursor: pointer;
        }
        
        .shift-block.full {
            background: #ffebee;
            border-color: #f44336;
        }
        
        .recruitment-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .recruitment-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .recruitment-date {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .recruitment-time {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .recruitment-slots {
            color: #666;
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .recruitment-actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 4px 8px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .action-btn.join {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .action-btn.cancel {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }
        
        .action-btn.detail {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .footer {
            text-align: center;
            margin: 20px 0;
        }
        
        .my-shifts-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <!-- ヘッダー -->
    <div class="header">
        <h1 data-bind="text: screenTitle">シフト一覧</h1>
        <div class="view-toggle">
            <button class="view-btn active" data-bind="click: function() { setView('month'); }">月</button>
            <button class="view-btn" data-bind="click: function() { setView('week'); }">週</button>
            <button class="view-btn" data-bind="click: function() { setView('day'); }">日</button>
            <button class="view-btn" data-bind="click: function() { setView('list'); }">リスト</button>
        </div>
        <a href="<?php echo \Uri::create('shifts/create'); ?>" class="btn">新規シフト登録</a>
    </div>
    
    <!-- アラート表示 -->
    <div data-bind="visible: alertMessage" class="alert" data-bind="css: { 'alert-success': alertType() === 'success', 'alert-error': alertType() === 'error' }">
        <span data-bind="text: alertMessage"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <!-- カレンダーセクション -->
        <div class="calendar-section">
            <div class="calendar-header">
                <div class="month-nav">
                    <button class="nav-btn" data-bind="click: previousMonth">‹</button>
                    <span class="current-month" data-bind="text: currentMonth"></span>
                    <button class="nav-btn" data-bind="click: nextMonth">›</button>
                </div>
            </div>
            
            <!-- カレンダーグリッド -->
            <div class="calendar-grid">
                <!-- 曜日ヘッダー -->
                <div class="calendar-day" style="background: #e9ecef; font-weight: bold; text-align: center;">月</div>
                <div class="calendar-day" style="background: #e9ecef; font-weight: bold; text-align: center;">火</div>
                <div class="calendar-day" style="background: #e9ecef; font-weight: bold; text-align: center;">水</div>
                <div class="calendar-day" style="background: #e9ecef; font-weight: bold; text-align: center;">木</div>
                <div class="calendar-day" style="background: #e9ecef; font-weight: bold; text-align: center;">金</div>
                <div class="calendar-day" style="background: #e9ecef; font-weight: bold; text-align: center;">土</div>
                <div class="calendar-day" style="background: #e9ecef; font-weight: bold; text-align: center;">日</div>
                
                <!-- カレンダー日付 -->
                <div id="calendar-days-container">
                    <!-- JavaScriptで動的に生成 -->
                </div>
            </div>
        </div>
        
        <!-- 募集中のシフトセクション -->
        <div class="recruitment-section">
            <div class="recruitment-header">募集中のシフト</div>
            
            <!-- ローディング表示 -->
            <div data-bind="visible: loading" class="loading">
                読み込み中...
            </div>
            
            <!-- 募集中のシフト一覧 -->
            <div data-bind="visible: !loading()">
                <div id="available-shifts-container">
                    <!-- JavaScriptで動的に生成 -->
                </div>
                
                <!-- 募集中のシフトが無い場合 -->
                <div id="no-shifts-message" style="display: none;">
                    <p>募集中のシフトはありません。</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- フッター -->
    <div class="footer">
        <button class="my-shifts-btn" data-bind="click: goToMyShifts">自分のシフト</button>
    </div>
    
    <script>
        // Knockout.js ViewModel
        function ShiftViewModel() {
            var self = this;
            
            // データ
            self.shifts = ko.observableArray([]);
            self.availableShifts = ko.observableArray([]);
            self.calendarDays = ko.observableArray([]);
            self.loading = ko.observable(false);
            self.alertMessage = ko.observable('');
            self.alertType = ko.observable('');
            self.currentView = ko.observable('month');
            self.currentDate = ko.observable(new Date(2025, 8, 1)); // 2025年9月に設定
            self.screenTitle = ko.observable('シフト一覧');
            
            // 現在の月を表示
            self.currentMonth = ko.computed(function() {
                var date = self.currentDate();
                return date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
            });
            
            // アラート表示
            self.showAlert = function(message, type) {
                self.alertMessage(message);
                self.alertType(type);
                setTimeout(function() {
                    self.alertMessage('');
                }, 5000);
            };
            
            // 表示切り替え
            self.setView = function(view) {
                self.currentView(view);
                $('.view-btn').removeClass('active');
                // アクティブボタンを設定
                $('.view-btn').each(function() {
                    if ($(this).text().trim() === getViewText(view)) {
                        $(this).addClass('active');
                    }
                });
                self.generateCalendar();
            };
            
            // ビューテキストを取得
            function getViewText(view) {
                switch(view) {
                    case 'month': return '月';
                    case 'week': return '週';
                    case 'day': return '日';
                    case 'list': return 'リスト';
                    default: return '月';
                }
            }
            
            // 前の月
            self.previousMonth = function() {
                var date = new Date(self.currentDate());
                date.setMonth(date.getMonth() - 1);
                self.currentDate(date);
                self.generateCalendar();
            };
            
            // 次の月
            self.nextMonth = function() {
                var date = new Date(self.currentDate());
                date.setMonth(date.getMonth() + 1);
                self.currentDate(date);
                self.generateCalendar();
            };
            
            // カレンダー生成
            self.generateCalendar = function() {
                var date = new Date(self.currentDate());
                var year = date.getFullYear();
                var month = date.getMonth();
                
                // 月の最初の日
                var firstDay = new Date(year, month, 1);
                var lastDay = new Date(year, month + 1, 0);
                
                // カレンダーの開始日（前月の日付も含む）
                var startDate = new Date(firstDay);
                startDate.setDate(startDate.getDate() - firstDay.getDay());
                
                var days = [];
                var currentDate = new Date(startDate);
                
                // 6週間分の日付を生成
                for (var i = 0; i < 42; i++) {
                    var dayShifts = self.shifts().filter(function(shift) {
                        var shiftDate = new Date(shift.shift_date);
                        return shiftDate.toDateString() === currentDate.toDateString();
                    });
                    
                    days.push({
                        day: currentDate.getDate(),
                        date: new Date(currentDate),
                        isWeekend: currentDate.getDay() === 0 || currentDate.getDay() === 6,
                        shifts: dayShifts
                    });
                    
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                
                // デバッグ用ログ
                console.log('Generated calendar days:', days);
                
                self.calendarDays(days);
                self.renderCalendarDays(days);
            };
            
            // カレンダー日付をレンダリング
            self.renderCalendarDays = function(days) {
                var container = document.getElementById('calendar-days-container');
                if (!container) return;
                
                container.innerHTML = '';
                
                days.forEach(function(day) {
                    var dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day';
                    if (day.isWeekend) {
                        dayElement.classList.add('weekend');
                    }
                    
                    var dayNumber = document.createElement('div');
                    dayNumber.className = 'day-number';
                    dayNumber.textContent = day.day;
                    dayElement.appendChild(dayNumber);
                    
                    // シフトブロックを追加
                    day.shifts.forEach(function(shift) {
                        var shiftBlock = document.createElement('div');
                        shiftBlock.className = 'shift-block';
                        if (shift.available_slots === 0) {
                            shiftBlock.classList.add('full');
                        }
                        
                        var timeDiv = document.createElement('div');
                        timeDiv.textContent = shift.start_time + '-' + shift.end_time;
                        shiftBlock.appendChild(timeDiv);
                        
                        var countDiv = document.createElement('div');
                        countDiv.textContent = shift.assigned_users.length + '/' + shift.slot_count;
                        shiftBlock.appendChild(countDiv);
                        
                        // クリックイベント
                        shiftBlock.addEventListener('click', function() {
                            self.viewShift(shift);
                        });
                        
                        dayElement.appendChild(shiftBlock);
                    });
                    
                    container.appendChild(dayElement);
                });
            };
            
            // 募集中のシフトをレンダリング
            self.renderAvailableShifts = function() {
                var container = document.getElementById('available-shifts-container');
                var noShiftsMessage = document.getElementById('no-shifts-message');
                
                if (!container) return;
                
                container.innerHTML = '';
                
                var availableShifts = self.availableShifts();
                
                if (availableShifts.length === 0) {
                    if (noShiftsMessage) {
                        noShiftsMessage.style.display = 'block';
                    }
                } else {
                    if (noShiftsMessage) {
                        noShiftsMessage.style.display = 'none';
                    }
                    
                    availableShifts.forEach(function(shift) {
                        var itemElement = document.createElement('div');
                        itemElement.className = 'recruitment-item';
                        
                        var dateDiv = document.createElement('div');
                        dateDiv.className = 'recruitment-date';
                        dateDiv.textContent = shift.shift_date;
                        itemElement.appendChild(dateDiv);
                        
                        var timeDiv = document.createElement('div');
                        timeDiv.className = 'recruitment-time';
                        timeDiv.textContent = shift.start_time + ' - ' + shift.end_time;
                        itemElement.appendChild(timeDiv);
                        
                        var slotsDiv = document.createElement('div');
                        slotsDiv.className = 'recruitment-slots';
                        slotsDiv.textContent = shift.assigned_users.length + '/' + shift.slot_count;
                        itemElement.appendChild(slotsDiv);
                        
                        var actionsDiv = document.createElement('div');
                        actionsDiv.className = 'recruitment-actions';
                        
                        var joinBtn = document.createElement('button');
                        joinBtn.className = 'action-btn join';
                        joinBtn.textContent = '参加';
                        joinBtn.addEventListener('click', function() {
                            self.joinShift(shift);
                        });
                        actionsDiv.appendChild(joinBtn);
                        
                        var cancelBtn = document.createElement('button');
                        cancelBtn.className = 'action-btn cancel';
                        cancelBtn.textContent = '取消';
                        cancelBtn.addEventListener('click', function() {
                            self.cancelShift(shift);
                        });
                        actionsDiv.appendChild(cancelBtn);
                        
                        var detailBtn = document.createElement('button');
                        detailBtn.className = 'action-btn detail';
                        detailBtn.textContent = '詳細';
                        detailBtn.addEventListener('click', function() {
                            self.viewShift(shift);
                        });
                        actionsDiv.appendChild(detailBtn);
                        
                        itemElement.appendChild(actionsDiv);
                        container.appendChild(itemElement);
                    });
                }
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
                            self.availableShifts(response.data.filter(function(shift) {
                                return shift.available_slots > 0;
                            }));
                            
                            // デバッグ用ログ
                            console.log('Loaded shifts:', response.data);
                            console.log('Available shifts:', self.availableShifts());
                            self.generateCalendar();
                            self.renderAvailableShifts();
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
            
            // シフト参加
            self.joinShift = function(shift) {
                $.ajax({
                    url: '<?php echo \Uri::create('api/shifts'); ?>/' + shift.id + '/join',
                    type: 'POST',
                    data: {
                        csrf_token: 'dummy_token' // 簡易実装
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('シフトに参加しました', 'success');
                            self.loadShifts();
                        } else {
                            self.showAlert('シフトの参加に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフトの参加に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // シフト取消
            self.cancelShift = function(shift) {
                if (!confirm('このシフトの参加を取り消しますか？')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo \Uri::create('api/shifts'); ?>/' + shift.id + '/cancel',
                    type: 'POST',
                    data: {
                        csrf_token: 'dummy_token' // 簡易実装
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('シフトの参加を取り消しました', 'success');
                            self.loadShifts();
                        } else {
                            self.showAlert('シフトの取消に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフトの取消に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // シフト詳細表示
            self.viewShift = function(shift) {
                window.location.href = '<?php echo \Uri::create('shifts'); ?>/' + shift.id;
            };
            
            // 自分のシフト画面へ
            self.goToMyShifts = function() {
                window.location.href = '<?php echo \Uri::create('my/shifts'); ?>';
            };
            
            // 初期化
            self.generateCalendar(); // カレンダーを先に生成
            self.loadShifts();
        }
        
        // ViewModelを適用
        ko.applyBindings(new ShiftViewModel());
    </script>
</body>
</html>