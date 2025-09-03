<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト一覧 - ShiftBoard</title>
    
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
        
        /* 月表示での募集中シフト表示 */
        .month-view .main-content {
            display: flex;
            gap: 20px;
        }
        
        .month-view .calendar-section {
            flex: 2;
        }
        
        .month-view .recruitment-section {
            flex: 1;
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
        
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            table-layout: fixed;
        }
        
        .calendar-table th {
            background: #4CAF50;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #45a049;
        }
        
        .calendar-table th:nth-child(1),
        .calendar-table th:nth-child(2),
        .calendar-table th:nth-child(3),
        .calendar-table th:nth-child(4),
        .calendar-table th:nth-child(5),
        .calendar-table th:nth-child(6),
        .calendar-table th:nth-child(7) {
            width: 14.28%; /* 月〜日を等間隔（7列で均等分割） */
        }
        
        .calendar-table td {
            border: 1px solid #e0e0e0;
            padding: 8px;
            vertical-align: top;
            min-height: 80px;
        }
        
        .calendar-table td:nth-child(1),
        .calendar-table td:nth-child(2),
        .calendar-table td:nth-child(3),
        .calendar-table td:nth-child(4),
        .calendar-table td:nth-child(5),
        .calendar-table td:nth-child(6),
        .calendar-table td:nth-child(7) {
            width: 14.28%; /* 月〜日を等間隔（7列で均等分割） */
        }
        
        .calendar-day {
            position: relative;
            min-height: 80px;
        }
        
        .calendar-day.other-month {
            background: #f8f9fa;
            color: #999;
        }
        
        .calendar-day.today {
            background: #e3f2fd;
            font-weight: bold;
        }
        
        .calendar-day.weekend {
            background: #fff3e0;
        }
        
        .day-number {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .shift-block {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 3px;
            padding: 2px 4px;
            margin: 1px 0;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .shift-block:hover {
            background: #bbdefb;
            transform: translateY(-1px);
        }
        
        .shift-block.full {
            background: #ffebee;
            border-color: #f44336;
        }
        
        .shift-block.full:hover {
            background: #ffcdd2;
        }
        
        .recruitment-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .recruitment-item {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 12px;
            background: #f8f9fa;
            transition: all 0.2s;
        }
        
        .recruitment-item:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .recruitment-date {
            font-weight: bold;
            margin-bottom: 6px;
            color: #333;
            font-size: 14px;
        }
        
        .recruitment-time {
            color: #666;
            font-size: 13px;
            margin-bottom: 6px;
        }
        
        .recruitment-slots {
            color: #888;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .recruitment-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 6px 10px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 11px;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
        }
        
        .action-btn.join {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .action-btn.join:hover {
            background: #218838;
        }
        
        .action-btn.cancel {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }
        
        .action-btn.cancel:hover {
            background: #c82333;
        }
        
        .action-btn.detail {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .action-btn.detail:hover {
            background: #0056b3;
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
        
        /* ビュー表示制御 */
        .view-content {
            display: none;
        }
        
        .view-content.active {
            display: block;
        }
        
        /* 週表示スタイル */
        .week-view {
            padding: 20px;
        }
        
        .week-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .week-day {
            background: white;
            padding: 15px;
            min-height: 120px;
        }
        
        .week-day-header {
            background: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            margin: -15px -15px 10px -15px;
        }
        
        .week-day.today .week-day-header {
            background: #2196F3;
        }
        
        .week-day.weekend .week-day-header {
            background: #FF9800;
        }
        
        .week-shifts {
            min-height: 80px;
        }
        
        .week-shift-item {
            background: #e3f2fd;
            border: 1px solid #2196F3;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 5px;
            font-size: 12px;
            cursor: pointer;
        }
        
        .week-shift-item.full {
            background: #ffebee;
            border-color: #f44336;
        }
        
        /* 日表示スタイル */
        .day-view {
            padding: 20px;
        }
        
        .day-header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }
        
        .day-shifts {
            background: white;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .day-shift-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .day-shift-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .day-shift-item.full {
            background: #ffebee;
            border-color: #f44336;
        }
        
        .day-shift-time {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .day-shift-slots {
            color: #666;
            margin-bottom: 10px;
        }
        
        .day-shift-note {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- ナビゲーションバー -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="<?php echo \Fuel\Core\Uri::create('shifts'); ?>" class="navbar-brand">ShiftBoard</a>
            <ul class="navbar-nav">
                <li><a href="<?php echo \Fuel\Core\Uri::create('shifts'); ?>" class="active">シフト一覧</a></li>
                <li><a href="<?php echo \Fuel\Core\Uri::create('shifts/create'); ?>">シフト作成</a></li>
                <li><a href="<?php echo \Fuel\Core\Uri::create('my/shifts'); ?>">自分のシフト</a></li>
                <li><a href="<?php echo \Fuel\Core\Uri::create('members'); ?>">メンバー管理</a></li>
            </ul>
        </div>
    </nav>

    <!-- ヘッダー -->
    <div class="header">
        <h1 data-bind="text: screenTitle">シフト一覧</h1>
        <div class="view-toggle">
            <button class="view-btn active" data-bind="click: function() { $root.setView('month'); }">月</button>
            <button class="view-btn" data-bind="click: function() { $root.setView('week'); }">週</button>
            <button class="view-btn" data-bind="click: function() { $root.setView('day'); }">日</button>
            <button class="view-btn" data-bind="click: function() { $root.setView('list'); }">リスト</button>
        </div>
        <a href="<?php echo \Fuel\Core\Uri::create('shifts/create'); ?>" class="btn">新規シフト登録</a>
    </div>
    
    <!-- アラート表示 -->
    <div data-bind="visible: alertMessage" class="alert" data-bind="css: { 'alert-success': alertType() === 'success', 'alert-error': alertType() === 'error' }">
        <span data-bind="text: alertMessage"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <!-- 月表示 -->
        <div class="view-content active month-view" data-bind="css: { active: currentView() === 'month' }">
            <div class="main-content">
                <div class="calendar-section">
                    <div class="calendar-header">
                        <div class="month-nav">
                            <button class="nav-btn" data-bind="click: $root.previousMonth">‹</button>
                            <span class="current-month" data-bind="text: currentMonth"></span>
                            <button class="nav-btn" data-bind="click: $root.nextMonth">›</button>
                        </div>
                    </div>
                    
                    <!-- カレンダーテーブル -->
                    <table class="calendar-table">
                        <thead>
                            <tr>
                                <th>月</th>
                                <th>火</th>
                                <th>水</th>
                                <th>木</th>
                                <th>金</th>
                                <th>土</th>
                                <th>日</th>
                            </tr>
                        </thead>
                        <tbody id="calendar-days-container">
                            <!-- JavaScriptで動的に生成 -->
                        </tbody>
                    </table>
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
        </div>
        
        <!-- 週表示 -->
        <div class="view-content week-view" data-bind="css: { active: currentView() === 'week' }">
            <div class="week-grid" id="week-grid-container">
                <!-- JavaScriptで動的に生成 -->
            </div>
        </div>
        
        <!-- 日表示 -->
        <div class="view-content day-view" data-bind="css: { active: currentView() === 'day' }">
            <div class="day-header" id="day-header-container">
                <!-- JavaScriptで動的に生成 -->
            </div>
            <div class="day-shifts" id="day-shifts-container">
                <!-- JavaScriptで動的に生成 -->
            </div>
        </div>
    </div>
    
    <!-- フッター -->
    <div class="footer">
        <button class="my-shifts-btn" data-bind="click: $root.goToMyShifts">自分のシフト</button>
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
            
            // アラート表示（共通機能を使用）
            self.showAlert = function(message, type) {
                ShiftBoard.alert.show(message, type);
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
                var view = self.currentView();
                
                if (view === 'month') {
                    self.generateMonthView();
                } else if (view === 'week') {
                    self.generateWeekView();
                } else if (view === 'day') {
                    self.generateDayView();
                }
            };
            
            // 月表示生成
            self.generateMonthView = function() {
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
            
            // 週表示生成
            self.generateWeekView = function() {
                var date = new Date(self.currentDate());
                var startOfWeek = new Date(date);
                startOfWeek.setDate(date.getDate() - date.getDay());
                
                var weekDays = [];
                for (var i = 0; i < 7; i++) {
                    var currentDate = new Date(startOfWeek);
                    currentDate.setDate(startOfWeek.getDate() + i);
                    
                    var dayShifts = self.shifts().filter(function(shift) {
                        var shiftDate = new Date(shift.shift_date);
                        return shiftDate.toDateString() === currentDate.toDateString();
                    });
                    
                    weekDays.push({
                        date: new Date(currentDate),
                        day: currentDate.getDate(),
                        dayOfWeek: currentDate.getDay(),
                        isWeekend: currentDate.getDay() === 0 || currentDate.getDay() === 6,
                        isToday: currentDate.toDateString() === new Date().toDateString(),
                        shifts: dayShifts
                    });
                }
                
                self.renderWeekView(weekDays);
            };
            
            // 日表示生成
            self.generateDayView = function() {
                var date = new Date(self.currentDate());
                var dayShifts = self.shifts().filter(function(shift) {
                    var shiftDate = new Date(shift.shift_date);
                    return shiftDate.toDateString() === date.toDateString();
                });
                
                self.renderDayView(date, dayShifts);
            };
            
            // カレンダー日付をレンダリング（テーブル形式）
            self.renderCalendarDays = function(days) {
                var container = document.getElementById('calendar-days-container');
                if (!container) return;
                
                container.innerHTML = '';
                
                // 6週間分の行を作成
                var weeks = [];
                for (var i = 0; i < 6; i++) {
                    weeks.push([]);
                }
                
                // 日付を週ごとにグループ化
                days.forEach(function(day, index) {
                    var weekIndex = Math.floor(index / 7);
                    if (weekIndex < 6) {
                        weeks[weekIndex].push(day);
                    }
                });
                
                // 各行（週）を作成
                weeks.forEach(function(week) {
                    var row = document.createElement('tr');
                    
                    week.forEach(function(day) {
                        var cell = document.createElement('td');
                        var dayElement = document.createElement('div');
                        dayElement.className = 'calendar-day';
                        
                        if (day.date.getMonth() !== self.currentDate().getMonth()) {
                            dayElement.classList.add('other-month');
                        }
                        
                        if (day.isWeekend) {
                            dayElement.classList.add('weekend');
                        }
                        
                        var today = new Date();
                        if (day.date.toDateString() === today.toDateString()) {
                            dayElement.classList.add('today');
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
                        
                        cell.appendChild(dayElement);
                        row.appendChild(cell);
                    });
                    
                    container.appendChild(row);
                });
            };
            
            // 週表示をレンダリング
            self.renderWeekView = function(weekDays) {
                var container = document.getElementById('week-grid-container');
                if (!container) return;
                
                container.innerHTML = '';
                
                var dayNames = ['日', '月', '火', '水', '木', '金', '土'];
                
                weekDays.forEach(function(day) {
                    var dayElement = document.createElement('div');
                    dayElement.className = 'week-day';
                    if (day.isToday) dayElement.classList.add('today');
                    if (day.isWeekend) dayElement.classList.add('weekend');
                    
                    var headerElement = document.createElement('div');
                    headerElement.className = 'week-day-header';
                    headerElement.textContent = dayNames[day.dayOfWeek] + ' ' + day.day;
                    dayElement.appendChild(headerElement);
                    
                    var shiftsElement = document.createElement('div');
                    shiftsElement.className = 'week-shifts';
                    
                    if (day.shifts.length === 0) {
                        shiftsElement.innerHTML = '<div style="color: #999; font-size: 12px;">シフトなし</div>';
                    } else {
                        day.shifts.forEach(function(shift) {
                            var shiftElement = document.createElement('div');
                            shiftElement.className = 'week-shift-item';
                            if (shift.assigned_users.length >= shift.slot_count) {
                                shiftElement.classList.add('full');
                            }
                            
                            shiftElement.innerHTML = 
                                '<div style="font-weight: bold;">' + shift.start_time + '-' + shift.end_time + '</div>' +
                                '<div style="font-size: 10px;">' + shift.assigned_users.length + '/' + shift.slot_count + '</div>';
                            
                            // クリックイベント
                            shiftElement.addEventListener('click', function() {
                                self.viewShift(shift);
                            });
                            
                            shiftsElement.appendChild(shiftElement);
                        });
                    }
                    
                    dayElement.appendChild(shiftsElement);
                    container.appendChild(dayElement);
                });
            };
            
            // 日表示をレンダリング
            self.renderDayView = function(date, dayShifts) {
                var headerContainer = document.getElementById('day-header-container');
                var shiftsContainer = document.getElementById('day-shifts-container');
                
                if (!headerContainer || !shiftsContainer) return;
                
                // ヘッダーを更新
                var dayNames = ['日', '月', '火', '水', '木', '金', '土'];
                var monthNames = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
                
                headerContainer.innerHTML = 
                    '<h2>' + date.getFullYear() + '年' + monthNames[date.getMonth()] + date.getDate() + '日（' + dayNames[date.getDay()] + '）</h2>';
                
                // シフト一覧を更新
                shiftsContainer.innerHTML = '';
                
                if (dayShifts.length === 0) {
                    shiftsContainer.innerHTML = '<div style="text-align: center; color: #999; padding: 40px;">この日のシフトはありません</div>';
                } else {
                    dayShifts.forEach(function(shift) {
                        var shiftElement = document.createElement('div');
                        shiftElement.className = 'day-shift-item';
                        if (shift.assigned_users.length >= shift.slot_count) {
                            shiftElement.classList.add('full');
                        }
                        
                        shiftElement.innerHTML = 
                            '<div class="day-shift-time">' + shift.start_time + ' - ' + shift.end_time + '</div>' +
                            '<div class="day-shift-slots">参加者: ' + shift.assigned_users.length + '/' + shift.slot_count + '人</div>' +
                            (shift.note ? '<div class="day-shift-note">' + shift.note + '</div>' : '');
                        
                        // クリックイベント
                        shiftElement.addEventListener('click', function() {
                            self.viewShift(shift);
                        });
                        
                        shiftsContainer.appendChild(shiftElement);
                    });
                }
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
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>',
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
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shift.id + '/join',
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
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shift.id + '/cancel',
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
                window.location.href = '<?php echo \Fuel\Core\Uri::create('shifts'); ?>/' + shift.id;
            };
            
            // 自分のシフト画面へ
            self.goToMyShifts = function() {
                window.location.href = '<?php echo \Fuel\Core\Uri::create('my/shifts'); ?>';
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