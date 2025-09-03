<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト一覧 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- シフト専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shifts.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>

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
    <div class="container">
        <div class="header-content">
            <div class="header-left">
                <div class="month-nav">
                    <button class="nav-btn" data-bind="click: $root.previousMonth"> ◀︎</button>
                    <span class="current-month" data-bind="text: currentMonth"></span>
                    <button class="nav-btn" data-bind="click: $root.nextMonth">▶︎ </button>
                </div>
                <div class="view-buttons">
                    <button class="view-btn active" data-bind="click: function() { $root.setView('month'); }">月</button>
                    <button class="view-btn" data-bind="click: function() { $root.setView('week'); }">週</button>
                    <button class="view-btn" data-bind="click: function() { $root.setView('day'); }">日</button>
                    <button class="view-btn">リスト</button>
                </div>
            </div>
            <div class="header-right">
                <a href="<?php echo \Fuel\Core\Uri::create('shifts/create'); ?>" class="btn">新規シフト登録</a>
            </div>
        </div>
    </div>
    </div>
    
    <!-- アラート表示 -->
    <div class="alert" data-bind="visible: alertMessage">
        <span data-bind="text: alertMessage"></span>
    </div>
    
    <!-- 動的アラートコンテナ -->
    <div id="alert-container"></div>
    
    <!-- メインコンテンツ -->
    <!-- 日表示 -->
    <div class="view-content day-view">
        <div class="day-main-content">
            <div class="day-calendar-section">
                <div class="day-calendar-header">
                    
                </div>
                <!-- 日表示用テーブル -->
                <table class="day-calendar-table">
                    <thead>
                        <tr>
                            <th>時間</th>
                            <th>シフト情報</th>
                            <th>参加者一覧</th>
                            <th>定員状況</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="day-shifts-container">
                        <!-- JavaScriptで動的に生成 -->
                    </tbody>
                </table>
                <!-- 自分のシフトボタン -->
        <div style="text-align: center; margin: 20px 40px;">
            <button class="btn my-shifts-btn" data-bind="click: goToMyShifts">自分のシフト</button>
        </div>
            </div>
            
            <!-- 募集中のシフトセクション -->
            <div class="day-recruitment-section">
                <div class="recruitment-header">募集中のシフト</div>
                
                <!-- ローディング表示 -->
                <div data-bind="visible: loading" class="loading">
                    読み込み中...
                </div>
                
                <!-- 募集中のシフト一覧 -->
                <div data-bind="visible: !loading()">
                    <div id="available-shifts-container-day">
                        <!-- JavaScriptで動的に生成 -->
                    </div>
                    
                    <!-- 募集中のシフトが無い場合 -->
                    <div id="no-shifts-message-day" style="display: none;">
                        <p>募集中のシフトはありません。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 週表示 -->
    <div class="view-content week-view">
        <div class="week-main-content">
            <div class="week-calendar-section">
                <div class="week-calendar-header">
                    <div class="week-nav">
                        <span class="current-week" data-bind="text: currentWeek"></span>
                    </div>
                </div>
                <!-- 週表示用テーブル -->
                <table class="week-calendar-table">
  <thead>
                        <tr>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                        </tr>
  </thead>
                    <tbody id="week-grid-container">
                        <!-- JavaScriptで動的に生成 -->
                    </tbody>
                </table>
                <!-- 自分のシフトボタン -->
        <div style="text-align: center; margin: 20px 40px;">
            <button class="btn my-shifts-btn" data-bind="click: goToMyShifts">自分のシフト</button>
        </div>
            </div>
            
            <!-- 募集中のシフトセクション -->
            <div class="week-recruitment-section">
                <div class="recruitment-header">募集中のシフト</div>
                
                <!-- ローディング表示 -->
                <div data-bind="visible: loading" class="loading">
                    読み込み中...
                </div>
                
                <!-- 募集中のシフト一覧 -->
                <div data-bind="visible: !loading()">
                    <div id="available-shifts-container-week">
                        <!-- JavaScriptで動的に生成 -->
                    </div>
                    
                    <!-- 募集中のシフトが無い場合 -->
                    <div id="no-shifts-message-week" style="display: none;">
                        <p>募集中のシフトはありません。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 月表示 -->
    <div class="view-content month-view active">
        <div class="month-main-content">
            <div class="month-calendar-section">
                <div class="month-calendar-header">
                    <div class="day-nav">
                        <span class="current-day" data-bind="text: currentDay"></span>
                    </div>
                </div>
                
                <!-- 月表示カレンダーテーブル -->
                <table class="month-calendar-table">
                    <thead>
                        <tr>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                            <th>Sun</th>
    </tr>
                    </thead>
                    <tbody id="calendar-days-container">
                        <!-- JavaScriptで動的に生成 -->
  </tbody>

</table>
        <!-- 自分のシフトボタン -->
        <div style="text-align: center; margin: 20px 40px;">
            <button class="btn my-shifts-btn" data-bind="click: goToMyShifts">自分のシフト</button>
        </div>
            </div>
            
            <!-- 募集中のシフトセクション -->
            <div class="month-recruitment-section">
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
            self.currentDate = ko.observable(new Date()); // 現在の日付に設定
            self.screenTitle = ko.observable('シフト一覧');
            
            // 現在の月を表示
            self.currentMonth = ko.computed(function() {
                var date = self.currentDate();
                return date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
            });
            
            // 現在の週を表示
            self.currentWeek = ko.computed(function() {
                var date = new Date(self.currentDate());
                var startOfWeek = new Date(date);
                startOfWeek.setDate(date.getDate() - date.getDay());
                var endOfWeek = new Date(startOfWeek.getTime());
                endOfWeek.setDate(startOfWeek.getDate() + 6);
                
                var startMonth = startOfWeek.getMonth() + 1;
                var endMonth = endOfWeek.getMonth() + 1;
                var startYear = startOfWeek.getFullYear();
                var endYear = endOfWeek.getFullYear();
                
                if (startYear === endYear && startMonth === endMonth) {
                    return startYear + '年' + startMonth + '月' + startOfWeek.getDate() + '日〜' + endOfWeek.getDate() + '日';
                } else if (startYear === endYear) {
                    return startYear + '年' + startMonth + '月' + startOfWeek.getDate() + '日〜' + endMonth + '月' + endOfWeek.getDate() + '日';
                } else {
                    return startYear + '年' + startMonth + '月' + startOfWeek.getDate() + '日〜' + endYear + '年' + endMonth + '月' + endOfWeek.getDate() + '日';
                }
            });
            
            // 現在の日を表示
            self.currentDay = ko.computed(function() {
                var date = self.currentDate();
                var dayNames = ['日', '月', '火', '水', '木', '金', '土'];
                return date.getFullYear() + '年' + (date.getMonth() + 1) + '月' + date.getDate() + '日（' + dayNames[date.getDay()] + '）';
            });
            
            // アラート表示（共通機能を使用）
            self.showAlert = function(message, type) {
                try {
                    // エラーの場合は表示時間を延長（10秒）
                    var duration = (type === 'error') ? 10000 : null;
                    ShiftBoard.alert.show(message, type, duration);
                } catch (e) {
                    console.error('Error in ShiftBoard.alert.show:', e);
                    // フォールバック: 古いアラート方式を使用
                    self.alertMessage(message);
                    self.alertType(type);
                }
            };
            
            // 表示切り替え
            self.setView = function(view) {
                console.log('setView called with:', view);
                self.currentView(view);
                
                // 日表示の場合は現在の日付に設定
                if (view === 'day') {
                    self.currentDate(new Date());
                }
                
                // すべてのビューコンテンツを非表示
                var viewContents = document.querySelectorAll('.view-content');
                console.log('Found view-content elements:', viewContents.length);
                viewContents.forEach(function(element) {
                    element.classList.remove('active');
                });
                
                // 選択されたビューを表示
                if (view === 'month') {
                    var monthView = document.querySelector('.month-view');
                    if (monthView) {
                        monthView.classList.add('active');
                        console.log('Added active to month-view');
                    }
                } else if (view === 'week') {
                    var weekView = document.querySelector('.week-view');
                    if (weekView) {
                        weekView.classList.add('active');
                        console.log('Added active to week-view');
                    }
                } else if (view === 'day') {
                    var dayView = document.querySelector('.day-view');
                    if (dayView) {
                        dayView.classList.add('active');
                        console.log('Added active to day-view');
                    }
                }
                
                // ボタンのアクティブ状態を更新
                var viewBtns = document.querySelectorAll('.view-btn');
                viewBtns.forEach(function(btn) {
                    btn.classList.remove('active');
                    if (btn.textContent.trim() === getViewText(view)) {
                        btn.classList.add('active');
                        console.log('Added active to button:', btn.textContent.trim());
                    }
                });
                
                self.generateCalendar();
                self.renderAvailableShifts();
            };
            
            // ビューテキストを取得
            function getViewText(view) {
                switch(view) {
                    case 'month': return '月';
                    case 'week': return '週';
                    case 'day': return '日';
                    default: return '月';
                }
            }
            
            // 前の月
            self.previousMonth = function() {
                var date = new Date(self.currentDate());
                date.setMonth(date.getMonth() - 1);
                self.currentDate(date);
                self.generateCalendar();
                self.renderAvailableShifts();
            };
            
            // 次の月
            self.nextMonth = function() {
                var date = new Date(self.currentDate());
                date.setMonth(date.getMonth() + 1);
                self.currentDate(date);
                self.generateCalendar();
                self.renderAvailableShifts();
            };
            
            // 前の週
            self.previousWeek = function() {
                var date = new Date(self.currentDate());
                date.setDate(date.getDate() - 7);
                self.currentDate(date);
                self.generateCalendar();
                self.renderAvailableShifts();
            };
            
            // 次の週
            self.nextWeek = function() {
                var date = new Date(self.currentDate());
                date.setDate(date.getDate() + 7);
                self.currentDate(date);
                self.generateCalendar();
                self.renderAvailableShifts();
            };
            
            // 前の日
            self.previousDay = function() {
                var date = new Date(self.currentDate());
                date.setDate(date.getDate() - 1);
                self.currentDate(date);
                self.generateCalendar();
                self.renderAvailableShifts();
            };
            
            // 次の日
            self.nextDay = function() {
                var date = new Date(self.currentDate());
                date.setDate(date.getDate() + 1);
                self.currentDate(date);
                self.generateCalendar();
                self.renderAvailableShifts();
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
                            timeDiv.className = 'shift-time';
                            timeDiv.textContent = shift.start_time + '-' + shift.end_time;
                            shiftBlock.appendChild(timeDiv);
                            
                            var countDiv = document.createElement('div');
                            countDiv.className = 'shift-count';
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
            
            // 週表示をレンダリング（テーブル形式）
            self.renderWeekView = function(weekDays) {
                var container = document.getElementById('week-grid-container');
                if (!container) return;
                
                container.innerHTML = '';
                
                // 1行のテーブルを作成
                var row = document.createElement('tr');
                
                weekDays.forEach(function(day) {
                    var cell = document.createElement('td');
                    var dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day';
                    
                    if (day.isToday) dayElement.classList.add('today');
                    if (day.isWeekend) dayElement.classList.add('weekend');
                    
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
                        timeDiv.className = 'shift-time';
                        timeDiv.textContent = shift.start_time + '-' + shift.end_time;
                        shiftBlock.appendChild(timeDiv);
                        
                        var countDiv = document.createElement('div');
                        countDiv.className = 'shift-count';
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
            };
            
            // 日表示をレンダリング（テーブル形式）
            self.renderDayView = function(date, dayShifts) {
                var shiftsContainer = document.getElementById('day-shifts-container');
                
                if (!shiftsContainer) return;
                
                // シフト一覧を更新
                shiftsContainer.innerHTML = '';
                
                if (dayShifts.length === 0) {
                    var row = document.createElement('tr');
                    var cell = document.createElement('td');
                    cell.colSpan = 5;
                    cell.style.textAlign = 'center';
                    cell.style.color = '#999';
                    cell.style.padding = '40px';
                    cell.textContent = 'この日のシフトはありません';
                    row.appendChild(cell);
                    shiftsContainer.appendChild(row);
                } else {
                    dayShifts.forEach(function(shift) {
                        var row = document.createElement('tr');
                        row.className = 'day-shift-row';
                        if (shift.assigned_users.length >= shift.slot_count) {
                            row.classList.add('full');
                        }
                        
                        // 時間列
                        var timeCell = document.createElement('td');
                        timeCell.className = 'day-shift-time';
                        timeCell.textContent = shift.start_time + ' - ' + shift.end_time;
                        row.appendChild(timeCell);
                        
                        // シフト情報列
                        var infoCell = document.createElement('td');
                        infoCell.className = 'day-shift-info';
                        var infoHtml = '';
                        if (shift.note) {
                            infoHtml += '<div class="day-shift-note">' + shift.note + '</div>';
                        }
                        infoHtml += '<div class="day-shift-id">ID: ' + shift.id + '</div>';
                        infoCell.innerHTML = infoHtml;
                        row.appendChild(infoCell);
                        
                        // 参加者一覧列
                        var participantsCell = document.createElement('td');
                        participantsCell.className = 'day-shift-participants';
                        if (shift.assigned_users.length === 0) {
                            participantsCell.innerHTML = '<span style="color: #999;">参加者なし</span>';
                        } else {
                            var participantsHtml = '';
                            shift.assigned_users.forEach(function(user) {
                                participantsHtml += '<div class="participant-item">' + user.name + ' (' + user.status + ')</div>';
                            });
                            participantsCell.innerHTML = participantsHtml;
                        }
                        row.appendChild(participantsCell);
                        
                        // 定員状況列
                        var statusCell = document.createElement('td');
                        statusCell.className = 'day-shift-status';
                        var availableSlots = shift.slot_count - shift.assigned_users.length;
                        var statusText = shift.assigned_users.length + '/' + shift.slot_count + '人';
                        if (availableSlots === 0) {
                            statusText += ' (満員)';
                            statusCell.style.color = '#d32f2f';
                            statusCell.style.fontWeight = 'bold';
                        } else {
                            statusText += ' (空き: ' + availableSlots + '人)';
                            statusCell.style.color = '#2e7d32';
                        }
                        statusCell.textContent = statusText;
                        row.appendChild(statusCell);
                        
                        // 操作列
                        var actionCell = document.createElement('td');
                        actionCell.className = 'day-shift-actions';
                        
                        var detailBtn = document.createElement('button');
                        detailBtn.className = 'action-btn detail';
                        detailBtn.textContent = '詳細';
                        detailBtn.addEventListener('click', function() {
                            self.viewShift(shift);
                        });
                        actionCell.appendChild(detailBtn);
                        
                        // 参加ボタン（空きがある場合）
                        if (availableSlots > 0) {
                            var joinBtn = document.createElement('button');
                            joinBtn.className = 'action-btn join';
                            joinBtn.textContent = '参加';
                            joinBtn.style.marginLeft = '5px';
                            joinBtn.addEventListener('click', function() {
                                self.joinShift(shift);
                            });
                            actionCell.appendChild(joinBtn);
                        }
                        
                        row.appendChild(actionCell);
                        shiftsContainer.appendChild(row);
                    });
                }
            };
            
            // 募集中のシフトをレンダリング
            self.renderAvailableShifts = function() {
                try {
                    var view = self.currentView();
                    var map = {
                        month: { container: 'available-shifts-container',      msg: 'no-shifts-message' },
                        week:  { container: 'available-shifts-container-week', msg: 'no-shifts-message-week' },
                        day:   { container: 'available-shifts-container-day',  msg: 'no-shifts-message-day' }
                    };
                    var target = map[view] || map.month;
                    self.renderAvailableShiftsForView(target.container, target.msg);
                } catch (error) {
                    console.error('Error in renderAvailableShifts:', error);
                }
            };
            
            // 特定のビュー用の募集中シフトをレンダリング
            self.renderAvailableShiftsForView = function(containerId, messageId) {
                console.log('=== renderAvailableShiftsForView ===');
                console.log('containerId:', containerId, 'messageId:', messageId);
                
                var container = document.getElementById(containerId);
                var noShiftsMessage = document.getElementById(messageId);
                
                if (!container) {
                    console.log('Container not found:', containerId);
                    return;
                }
                
                console.log('Container found:', container);
                container.innerHTML = '';
                
                var availableShifts = self.availableShifts();
                console.log('Available shifts from observable:', availableShifts);
                console.log('Available shifts length:', availableShifts.length);
                
                if (availableShifts.length === 0) {
                    console.log('No available shifts, showing message');
                    if (noShiftsMessage) {
                        noShiftsMessage.style.display = 'block';
                    }
                } else {
                    console.log('Found available shifts, rendering items');
                    if (noShiftsMessage) {
                        noShiftsMessage.style.display = 'none';
                    }
                    
                    availableShifts.forEach(function(shift, index) {
                        console.log('Creating shift item', index + 1, 'for:', shift);
                        var itemElement = document.createElement('div');
                        itemElement.className = 'recruitment-item';
                        
                        // シフト情報とボタンを横並びにするコンテナ
                        var infoContainer = document.createElement('div');
                        infoContainer.style.cssText = 'display: flex; justify-content: space-between; align-items: center; width: 100%;';
                        
                        // 左側のシフト情報
                        var shiftInfo = document.createElement('div');
                        shiftInfo.style.cssText = 'flex: 1;';
                        
                        var dateDiv = document.createElement('div');
                        dateDiv.className = 'recruitment-date';
                        dateDiv.textContent = shift.shift_date + '  ' +  shift.start_time + ' - ' + shift.end_time;
                        dateDiv.style.cssText = 'font-weight: bold; margin-bottom: 4px;';
                        shiftInfo.appendChild(dateDiv);
                        
                        var slotsDiv = document.createElement('div');
                        slotsDiv.className = 'recruitment-slots';
                        slotsDiv.textContent = '空き: ' + shift.available_slots + '人 / 定員: ' + shift.slot_count + '人';
                        slotsDiv.style.cssText = 'font-size: 12px; color: #666;';
                        shiftInfo.appendChild(slotsDiv);
                        
                        infoContainer.appendChild(shiftInfo);
                        
                        // 右側のボタン
                        var actionsDiv = document.createElement('div');
                        actionsDiv.className = 'recruitment-actions';
                        actionsDiv.style.cssText = 'display: flex; gap: 5px; flex-shrink: 0;';
                        
                        var joinBtn = document.createElement('button');
                        joinBtn.className = 'action-btn join';
                        joinBtn.textContent = '参加';
                        joinBtn.style.cssText = 'padding: 4px 8px; font-size: 11px; border: 1px solid #28a745; background: #28a745; color: white; border-radius: 3px; cursor: pointer;';
                        joinBtn.addEventListener('click', function() {
                            self.joinShift(shift);
                        });
                        actionsDiv.appendChild(joinBtn);
                        
                        var cancelBtn = document.createElement('button');
                        cancelBtn.className = 'action-btn cancel';
                        cancelBtn.textContent = '取消';
                        cancelBtn.style.cssText = 'padding: 4px 8px; font-size: 11px; border: 1px solid #dc3545; background: #dc3545; color: white; border-radius: 3px; cursor: pointer;';
                        cancelBtn.addEventListener('click', function() {
                            self.cancelShift(shift);
                        });
                        actionsDiv.appendChild(cancelBtn);
                        
                        var detailBtn = document.createElement('button');
                        detailBtn.className = 'action-btn detail';
                        detailBtn.textContent = '詳細';
                        detailBtn.style.cssText = 'padding: 4px 8px; font-size: 11px; border: 1px solid #007bff; background: #007bff; color: white; border-radius: 3px; cursor: pointer;';
                        detailBtn.addEventListener('click', function() {
                            self.viewShift(shift);
                        });
                        actionsDiv.appendChild(detailBtn);
                        
                        infoContainer.appendChild(actionsDiv);
                        itemElement.appendChild(infoContainer);
                        container.appendChild(itemElement);
                        console.log('Added shift item to container');
                        console.log('Container innerHTML length:', container.innerHTML.length);
                        console.log('Container children count:', container.children.length);
                    });
                }
                console.log('=== End renderAvailableShiftsForView ===');
            };
            
            // シフト一覧を取得
            self.loadShifts = function() {
                self.loading(true);
                // 取得レンジを指定（APIが期間必須でも動くように）
                (function(){
                    var base = new Date(self.currentDate());
                    var y = base.getFullYear(), m = base.getMonth();
                    var first = new Date(y, m, 1);
                    var last  = new Date(y, m + 1, 0);
                    // 前後1週間バッファ
                    first.setDate(first.getDate() - 7);
                    last.setDate(last.getDate() + 7);
                    function fmt(d){ var z=n=>String(n).padStart(2,'0'); return d.getFullYear()+'-'+z(d.getMonth()+1)+'-'+z(d.getDate()); }
                    self._from = fmt(first);
                    self._to   = fmt(last);
                    console.log('[loadShifts] range', self._from, '→', self._to);
                })();
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>',
                    data: { from: self._from, to: self._to },
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            console.log('=== API response ===');
                            console.log('Full response:', response);
                            console.log('Total shifts:', response.data.length);

                            // 正規化：型を数値に統一し、available_slots を算出
                            var normalized = (response.data || []).map(function(shift) {
                                var assignedCount = Array.isArray(shift.assigned_users)
                                    ? shift.assigned_users.length
                                    : Number(shift.assigned_count ?? 0);
                                var slotCount = Number((shift.slot_count ?? shift.capacity ?? 0));
                                var available = (shift.available_slots != null)
                                    ? Number(shift.available_slots)
                                    : Math.max(slotCount - assignedCount, 0);
                                return Object.assign({}, shift, {
                                    assigned_users: Array.isArray(shift.assigned_users) ?
                                    shift.assigned_users : [],
                                    slot_count: slotCount,
                                    available_slots: available
                                });
                            });

                            self.shifts(normalized);

                            // 募集中のみ抽出（available_slots > 0）
                            var availableShifts = normalized.filter(function(shift) {
                                console.log('Checking shift ID:', shift.id, 'available_slots:', shift.available_slots);
                                return shift.available_slots > 0;
                            });
                            console.log('=== Available shifts ===');
                            console.log('Filtered available shifts:', availableShifts);
                            console.log('Available shifts count:', availableShifts.length);
                            self.availableShifts(availableShifts);
                            console.log('=== After setting availableShifts ===');
                            console.log('self.availableShifts():', self.availableShifts());
                        } else {
                            self.showAlert('シフト一覧の取得に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフト一覧の取得に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        // ★ visible 条件を満たしてから描画
                        self.loading(false);
                        self.generateCalendar();
                        self.renderAvailableShifts();
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
                    success: function(response, status, xhr) {
                        // 成功レスポンス
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.success) {
                                self.showAlert('シフトに参加しました', 'success');
                                self.loadShifts();
                            } else {
                                self.showAlert('シフトの参加に失敗しました: ' + data.message, 'error');
                            }
                        } catch (e) {
                            self.showAlert('シフトの参加に失敗しました', 'error');
                            console.error('JSON Parse Error:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        // エラーメッセージを初期化
                        var errorMessage = 'シフトの参加に失敗しました';
                        
                        // ステータスコード別の処理
                        if (xhr.status === 409) {
                            // Conflictエラーの場合
                            try {
                                var response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || '既に参加しているか、定員に達しています';
                            } catch (e) {
                                errorMessage = '既に参加しているか、定員に達しています';
                            }
                        } else if (xhr.status === 404) {
                            errorMessage = 'シフトが見つかりません';
                        } else if (xhr.status === 500) {
                            errorMessage = 'サーバーエラーが発生しました';
                        }
                        
                        // エラーメッセージを表示
                        self.showAlert(errorMessage, 'error');
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
                    success: function(response, status, xhr) {
                        // レスポンスを手動でJSONパース
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (data.success) {
                                self.showAlert('シフトの参加を取り消しました', 'success');
                                self.loadShifts();
                            } else {
                                self.showAlert('シフトの取消に失敗しました: ' + data.message, 'error');
                            }
                        } catch (e) {
                            self.showAlert('シフトの取消に失敗しました', 'error');
                            console.error('JSON Parse Error:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = 'シフトの取消に失敗しました';
                        
                        if (xhr.status === 404) {
                            errorMessage = 'このシフトに参加していません';
                        } else if (xhr.status === 409) {
                            errorMessage = 'シフトの取消ができません';
                        }
                        
                        self.showAlert(errorMessage, 'error');
                        console.error('AJAX Error:', error, xhr.responseText);
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
            self.setView('month'); // 初期表示を月表示に設定
            self.generateCalendar(); // カレンダーを先に生成
            self.loadShifts();
        }
        
        // ViewModelを適用
        ko.applyBindings(new ShiftViewModel());
    </script>
</body>
</html>
