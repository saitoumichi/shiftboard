// シフト一覧ページ用JavaScript

// ShiftViewModelクラス
function ShiftViewModel() {
    var self = this;
    
    // データ
    self.currentDate = ko.observable(new Date());
    self.currentView = ko.observable('month');
    self.shifts = ko.observableArray([]);
    self.availableShifts = ko.observableArray([]);
    self.calendarDays = ko.observableArray([]);
    self.loading = ko.observable(false);
    self.alertMessage = ko.observable('');
    self.alertType = ko.observable('');
    
    // 計算プロパティ
    self.currentMonth = ko.computed(function() {
        var date = self.currentDate();
        return date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
    });
    
    self.currentWeek = ko.computed(function() {
        var date = new Date(self.currentDate());
        var startOfWeek = new Date(date);
        startOfWeek.setDate(date.getDate() - date.getDay());
        var endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        return (startOfWeek.getMonth() + 1) + '/' + startOfWeek.getDate() + ' - ' + 
               (endOfWeek.getMonth() + 1) + '/' + endOfWeek.getDate();
    });
    
    self.currentDay = ko.computed(function() {
        var date = self.currentDate();
        return (date.getMonth() + 1) + '月' + date.getDate() + '日';
    });
    
    // アラート表示
    self.showAlert = function(message, type) {
        // 新しいアラート方式を試す
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        } else {
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
        } else if (view === 'list') {
            var listView = document.querySelector('.list-view');
            if (listView) {
                listView.classList.add('active');
                console.log('Added active to list-view');
                console.log('List view display set to block');
                
                // 強制的に表示
                listView.style.setProperty('display', 'block', 'important');
                listView.style.setProperty('visibility', 'visible', 'important');
                listView.style.setProperty('opacity', '1', 'important');
                
                // 他のビューを非表示にする
                var otherViews = document.querySelectorAll('.day-recruitment-section, .week-recruitment-section, .month-recruitment-section');
                console.log('Other views found:', otherViews.length);
                otherViews.forEach(function(view) {
                    view.style.display = 'none';
                });
                self.renderListShifts();
            } else {
                console.error('List view element not found!');
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
            case 'list': return 'リスト';
            default: return '月';
        }
    }
    
    // 前の月
    self.previousMonth = function() {
        console.log('=== PREVIOUS MONTH ===');
        var date = new Date(self.currentDate());
        console.log('Before change:', date.getFullYear(), '年', date.getMonth() + 1, '月');
        
        // 月の1日に設定してから月を変更（月末問題を回避）
        date.setDate(1);
        date.setMonth(date.getMonth() - 1);
        console.log('After change:', date.getFullYear(), '年', date.getMonth() + 1, '月');
        
        self.currentDate(date);
        
        // 手動で日付表示を更新
        var monthDisplay = document.querySelector('.current-month');
        if (monthDisplay) {
            var newDateText = self.currentDay();
            console.log('Setting display to:', newDateText);
            monthDisplay.textContent = newDateText;
        }
        
        self.generateCalendar();
        self.loadShifts(); // シフトを再読み込み
        self.renderAvailableShifts();
    };
    
    // 次の月
    self.nextMonth = function() {
        console.log('=== NEXT MONTH ===');
        var date = new Date(self.currentDate());
        console.log('Before change:', date.getFullYear(), '年', date.getMonth() + 1, '月');
        
        // 月の1日に設定してから月を変更（月末問題を回避）
        date.setDate(1);
        date.setMonth(date.getMonth() + 1);
        console.log('After change:', date.getFullYear(), '年', date.getMonth() + 1, '月');
        
        self.currentDate(date);
        
        // 手動で日付表示を更新
        var monthDisplay = document.querySelector('.current-month');
        if (monthDisplay) {
            var newDateText = self.currentDay();
            console.log('Setting display to:', newDateText);
            monthDisplay.textContent = newDateText;
        }
        
        self.generateCalendar();
        self.loadShifts(); // シフトを再読み込み
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
                
                // 日付セルにクリックイベントを追加
                dayElement.addEventListener('click', function() {
                    // その日のシフトがある場合は最初のシフトの詳細を表示
                    if (day.shifts.length > 0) {
                        self.viewShift(day.shifts[0]);
                    } else {
                        // シフトがない場合はその日の日付でシフト作成ページに遷移
                        var dateStr = day.date.getFullYear() + '-' + 
                                    String(day.date.getMonth() + 1).padStart(2, '0') + '-' + 
                                    String(day.date.getDate()).padStart(2, '0');
                        window.location.href = '/shifts/create?date=' + dateStr;
                    }
                });
                
                // 日付セルをクリック可能にするスタイル
                dayElement.style.cursor = 'pointer';
                
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
            
            // 日付セルにクリックイベントを追加
            dayElement.addEventListener('click', function() {
                // その日のシフトがある場合は最初のシフトの詳細を表示
                if (day.shifts.length > 0) {
                    self.viewShift(day.shifts[0]);
                } else {
                    // シフトがない場合はその日の日付でシフト作成ページに遷移
                    var dateStr = day.date.getFullYear() + '-' + 
                                String(day.date.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(day.date.getDate()).padStart(2, '0');
                    window.location.href = '/shifts/create?date=' + dateStr;
                }
            });
            
            // 日付セルをクリック可能にするスタイル
            dayElement.style.cursor = 'pointer';
            
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
                timeCell.textContent = shift.start_time + '-' + shift.end_time;
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
            self.renderAvailableShiftsForView(view, target.container, target.msg);
        } catch (error) {
            console.error('Error in renderAvailableShifts:', error);
        }
    };
    
    // 特定のビュー用の募集中シフトをレンダリング
    self.renderAvailableShiftsForView = function(view, containerId, messageId) {
        console.log('=== renderAvailableShiftsForView ===');
        console.log('view:', view, 'containerId:', containerId, 'messageId:', messageId);
        
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
        
        // 週表記の場合は、その週のシフトのみをフィルタリング
        var filteredShifts = availableShifts;
        if (view === 'week') {
            var base = new Date(self.currentDate());
            var weekStart = new Date(base);
            weekStart.setHours(0,0,0,0);
            weekStart.setDate(base.getDate() - base.getDay()); // Sun
            var weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6); // Sat
            weekEnd.setHours(23,59,59,999);
            console.log('Week view - filtering shifts for week:', weekStart, 'to', weekEnd);

            filteredShifts = availableShifts.filter(function(shift) {
                var sd = new Date(shift.shift_date); // ローカル日付
                return sd && sd >= weekStart && sd <= weekEnd;
            });
            console.log('Filtered shifts for week (count):', filteredShifts.length);
        }

        // 日表示の場合は、その日のシフトのみをフィルタリング
        if (view === 'day') {
            var cur = new Date(self.currentDate());
            cur.setHours(0,0,0,0);
            var next = new Date(cur);
            next.setDate(cur.getDate() + 1);
            next.setMilliseconds(-1);
            filteredShifts = availableShifts.filter(function(shift) {
                var sd = new Date(shift.shift_date);
                return sd && sd >= cur && sd <= next;
            });
        }
        
        if (filteredShifts.length === 0) {
            console.log('No available shifts for current view, showing message');
            if (noShiftsMessage) {
                noShiftsMessage.style.display = 'block';
            }
        } else {
            console.log('Found available shifts, rendering items');
            if (noShiftsMessage) {
                noShiftsMessage.style.display = 'none';
            }
            
            filteredShifts.forEach(function(shift, index) {
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
                // 秒数表記を削除（HH:MM:SS → HH:MM）
                var startTime = shift.start_time.substring(0, 5);
                var endTime = shift.end_time.substring(0, 5);
                dateDiv.textContent = shift.shift_date + '  ' + startTime + '-' + endTime;
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
    
    // シフト詳細表示
    self.viewShift = function(shift) {
        window.location.href = '/shifts/' + shift.id;
    };
    
    // 自分のシフト画面へ
    self.goToMyShifts = function() {
        window.location.href = '/my/shifts';
    };
    
    // シフト一覧を取得
    self.loadShifts = function() {
        self.loading(true);
        // 取得レンジを指定（APIが期間必須でも動くように）
        (function(){
            var base = new Date(self.currentDate());
            console.log('[loadShifts] currentDate:', base);
            var y = base.getFullYear(), m = base.getMonth();
            console.log('[loadShifts] year:', y, 'month:', m);
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
            url: '/api/shifts',
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
            url: '/api/shifts/' + shift.id + '/join',
            type: 'POST',
            data: {
                csrf_token: 'dummy_token' // 簡易実装
            },
            success: function(response, status, xhr) {
                // 成功レスポンス
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        self.showAlert('シフトに参加しました！自分のシフトページで確認できます。', 'success');
                        self.loadShifts();
                        
                        // 自分のシフトページのデータも更新
                        if (typeof window.refreshMyShifts === 'function') {
                            window.refreshMyShifts();
                        }
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
            url: '/api/shifts/' + shift.id + '/cancel',
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
                        
                        // 自分のシフトページのデータも更新
                        if (typeof window.refreshMyShifts === 'function') {
                            window.refreshMyShifts();
                        }
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
    
    // リスト表示のシフトをレンダリング
    self.renderListShifts = function() {
        console.log('Rendering list shifts...');
        
        var container = document.getElementById('available-shifts-container-list');
        var noShiftsMessage = document.getElementById('no-shifts-message-list');
        
        console.log('Container found:', !!container);
        console.log('No shifts message found:', !!noShiftsMessage);
        
        if (!container) {
            console.error('List container not found');
            return;
        }
        
        // コンテナをクリア
        container.innerHTML = '';
        
        // 全てのシフトを取得
        self.loadAllShiftsForList();
    };
    
    // リスト表示用に全てのシフトを取得
    self.loadAllShiftsForList = function() {
        console.log('Loading all shifts for list...');
        
        $.ajax({
            url: '/api/shifts',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('All shifts API response:', response);
                
                if (response.success && response.data) {
                    var shifts = response.data;
                    console.log('All shifts loaded:', shifts.length);
                    self.renderShiftsList(shifts);
                } else {
                    console.error('Failed to load shifts:', response);
                    self.showNoShiftsMessage();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading shifts:', error);
                self.showNoShiftsMessage();
            }
        });
    };
    
    // シフトリストをレンダリング
    self.renderShiftsList = function(shifts) {
        var container = document.getElementById('available-shifts-container-list');
        var noShiftsMessage = document.getElementById('no-shifts-message-list');
        
        console.log('renderShiftsList called with:', shifts ? shifts.length : 'null', 'shifts');
        console.log('Container found:', !!container);
        console.log('No shifts message found:', !!noShiftsMessage);
        
        if (!shifts || shifts.length === 0) {
            console.log('No shifts to display, showing no shifts message');
            self.showNoShiftsMessage();
            return;
        }
        
        console.log('Rendering', shifts.length, 'shifts to list');
        
        // シフトをリスト形式で表示
        shifts.forEach(function(shift) {
            var shiftItem = document.createElement('div');
            shiftItem.className = 'shift-list-item';
            
            var shiftInfo = document.createElement('div');
            shiftInfo.className = 'shift-list-info';
            
            var title = document.createElement('div');
            title.className = 'shift-list-title';
            title.textContent = shift.title || 'シフト';
            
            var details = document.createElement('div');
            details.className = 'shift-list-details';
            details.textContent = shift.shift_date + ' ' + shift.start_time + '〜' + shift.end_time;
            
            shiftInfo.appendChild(title);
            shiftInfo.appendChild(details);
            
            var actions = document.createElement('div');
            actions.className = 'shift-list-actions';
            
            var viewBtn = document.createElement('button');
            viewBtn.className = 'shift-list-btn primary';
            viewBtn.textContent = '詳細';
            viewBtn.onclick = function() {
                window.location.href = '/shifts/' + shift.id;
            };
            
            var joinBtn = document.createElement('button');
            joinBtn.className = 'shift-list-btn secondary';
            joinBtn.textContent = '参加';
            joinBtn.onclick = function() {
                self.joinShift(shift.id);
            };
            
            actions.appendChild(viewBtn);
            actions.appendChild(joinBtn);
            
            shiftItem.appendChild(shiftInfo);
            shiftItem.appendChild(actions);
            
            container.appendChild(shiftItem);
            console.log('Added shift item to list container:', shift.title);
        });
        
        console.log('List rendering complete. Container children count:', container.children.length);
        
        // リストビューの表示状態を確認
        var listView = document.querySelector('.list-recruitment-section');
        if (listView) {
            console.log('List view display style:', listView.style.display);
            console.log('List view computed display:', window.getComputedStyle(listView).display);
            console.log('List view visibility:', window.getComputedStyle(listView).visibility);
            console.log('List view height:', window.getComputedStyle(listView).height);
        }
        
        // コンテナの表示状態を確認
        console.log('Container display style:', container.style.display);
        console.log('Container computed display:', window.getComputedStyle(container).display);
        console.log('Container height:', window.getComputedStyle(container).height);
        
        if (noShiftsMessage) {
            noShiftsMessage.style.display = 'none';
        }
    };
    
    // シフトなしメッセージを表示
    self.showNoShiftsMessage = function() {
        var noShiftsMessage = document.getElementById('no-shifts-message-list');
        if (noShiftsMessage) {
            noShiftsMessage.style.display = 'block';
        }
    };
    
    // 初期化
    self.setView('month'); // 初期表示を月表示に設定
    self.generateCalendar(); // カレンダーを先に生成
    self.loadShifts();
    
    // 手動でナビゲーションボタンのイベントリスナーを追加
    setTimeout(function() {
        var prevBtn = document.querySelector('.nav-btn[data-bind*="previousMonth"]');
        var nextBtn = document.querySelector('.nav-btn[data-bind*="nextMonth"]');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.previousMonth();
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.nextMonth();
            });
        }
    }, 1000);
}
