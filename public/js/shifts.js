// シフト一覧ページ用JavaScript - キャッシュ無効化 v2

// 参照専用。再代入や再宣言はしない！
const uid = window.CURRENT_USER_ID;

// API_BASEとCURRENT_USER_IDはビューファイルで設定済み

// 未ログインガード（即リダイレクトは削除）
// 代わりに、DOM後に"操作を無効化"するだけ
document.addEventListener('DOMContentLoaded', function () {
  const uid = Number(window.CURRENT_USER_ID || document.querySelector('meta[name="current-user-id"]')?.content || 0);

  if (!uid) {

    // 参加・取消ボタンを無効化（存在すれば）
    const btns = document.querySelectorAll('.action-btn.btn-participate, .action-btn.btn-cancel, .btn-join, .btn-cancel-shift, .btn.my-shifts-btn, .nav-btn, .view-btn');

    btns.forEach(b => { b.disabled = true; b.title = 'ログインが必要です'; });
    // ここで location.href に飛ばさない
  } else {

    // ログイン済みの場合はボタンを有効化
    const btns = document.querySelectorAll('.action-btn.btn-participate, .action-btn.btn-cancel, .btn-join, .btn-cancel-shift, .btn.my-shifts-btn, .nav-btn, .view-btn');

    btns.forEach(b => { b.disabled = false; b.title = ''; });
  }
});

// ShiftViewModelクラス
function ShiftViewModel() {
    const self = this;
    
    // データ
    self.filter = ko.observable('all'); // 'all', 'open', 'full', 'mine'
    self.currentDate = ko.observable(new Date());
    self.currentView = ko.observable('month');
    self.shifts = ko.observableArray([]);
    self.availableShifts = ko.observableArray([]);
    self.calendarDays = ko.observableArray([]);
    self.loading = ko.observable(false);
    self.alertMessage = ko.observable('');
    self.alertType = ko.observable('');
    
    // 計算プロパティ：フィルタリングされたシフト一覧
    self.filteredShifts = ko.computed(function() {
        const allShifts = self.shifts();
        const currentFilter = self.filter();

        if (currentFilter === 'open') {
            return allShifts.filter(function(shift) {
                return shift.available_slots > 0;
            });
        }
        if (currentFilter === 'full') {
            return allShifts.filter(function(shift) {
                return shift.available_slots === 0;
            });
        }
        if (currentFilter === 'mine') {
            return allShifts.filter(function(shift) {
                // 参加ユーザーに自分自身が含まれているかチェック
                return shift.assigned_users.some(function(user) {
                    return user.id === window.CURRENT_USER_ID;
                });
            });
        }
        return allShifts; // 'all'の場合はすべて返す
    });
    
    // 計算プロパティ - 月表示
    self.currentMonth = ko.computed(function() {
        const date = self.currentDate();
        return date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
    });
    
    self.currentWeek = ko.computed(function() {
        const date = new Date(self.currentDate());
        const startOfWeek = new Date(date);
        startOfWeek.setDate(date.getDate() - date.getDay());
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        return (startOfWeek.getMonth() + 1) + '/' + startOfWeek.getDate() + ' - ' + 
               (endOfWeek.getMonth() + 1) + '/' + endOfWeek.getDate();
    });
    
    self.currentDay = ko.computed(function() {
        const date = self.currentDate();
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

        self.currentView(view);
        
        // 日表示の場合は現在の日付に設定
        if (view === 'day') {
            self.currentDate(new Date());
        }
        
        // すべてのビューコンテンツを非表示
        const viewContents = document.querySelectorAll('.view-content');

        viewContents.forEach(function(element) {
            element.classList.remove('active');
        });
        
        // 選択されたビューを表示
        if (view === 'month') {
            const monthView = document.querySelector('.month-view');
            if (monthView) {
                monthView.classList.add('active');

            }
        } else if (view === 'week') {
            const weekView = document.querySelector('.week-view');
            if (weekView) {
                weekView.classList.add('active');

            }
        } else if (view === 'day') {
            const dayView = document.querySelector('.day-view');
            if (dayView) {
                dayView.classList.add('active');

            }
        } else if (view === 'list') {
            const listView = document.querySelector('.list-view');
            if (listView) {
                listView.classList.add('active');

                // 強制的に表示
                listView.style.setProperty('display', 'block', 'important');
                listView.style.setProperty('visibility', 'visible', 'important');
                listView.style.setProperty('opacity', '1', 'important');
                
                // 他のビューを非表示にする
                const otherViews = document.querySelectorAll('.day-recruitment-section, .week-recruitment-section, .month-recruitment-section');

                otherViews.forEach(function(view) {
                    view.style.display = 'none';
                });
                self.renderListShifts();
            } else {

            }
        }
        
        // ボタンのアクティブ状態を更新
        const viewBtns = document.querySelectorAll('.view-btn');
        viewBtns.forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.textContent.trim() === self.getViewText(view)) {
                btn.classList.add('active');

            }
        });
        
        self.generateCalendar();
        self.renderAvailableShifts();
    };
    
    // ビューテキストを取得
    self.getViewText = function(view) {
        switch(view) {
            case 'month': return '月';
            case 'week': return '週';
            case 'day': return '日';
            case 'list': return 'リスト';
            default: return '月';
        }
    };
    
    // 前の月（実行回数を制限）
    let isChangingMonthPrev = false;
    self.previousMonth = function() {
        if (isChangingMonthPrev) return;
        isChangingMonthPrev = true;
        
        let date = new Date(self.currentDate());
        date.setDate(1);
        date.setMonth(date.getMonth() - 1);
        self.currentDate(date);
        
        // DOM直接更新
        const monthEl = document.querySelector('.current-month');
        if (monthEl) {
            monthEl.textContent = date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
        }
        
        self.loadShifts();
        
        setTimeout(() => { isChangingMonthPrev = false; }, 300);
    };
    
    // 次の月（実行回数を制限）
    let isChangingMonth = false;
    self.nextMonth = function() {
        if (isChangingMonth) return;
        isChangingMonth = true;
        
        let date = new Date(self.currentDate());
        date.setDate(1);
        date.setMonth(date.getMonth() + 1);
        self.currentDate(date);
        
        // DOM直接更新
        const monthEl = document.querySelector('.current-month');
        if (monthEl) {
            monthEl.textContent = date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
        }
        
        self.loadShifts();
        
        setTimeout(() => { isChangingMonth = false; }, 300);
    };
    
    // 前の週
    self.previousWeek = function() {
        let date = new Date(self.currentDate());
        date.setDate(date.getDate() - 7);
        self.currentDate(date);
        self.generateCalendar();
        self.renderAvailableShifts();
    };
    
    // 次の週
    self.nextWeek = function() {
        let date = new Date(self.currentDate());
        date.setDate(date.getDate() + 7);
        self.currentDate(date);
        self.generateCalendar();
        self.renderAvailableShifts();
    };
    
    // 前の日
    self.previousDay = function() {
        let date = new Date(self.currentDate());
        date.setDate(date.getDate() - 1);
        self.currentDate(date);
        self.generateCalendar();
        self.renderAvailableShifts();
    };
    
    // 次の日
    self.nextDay = function() {
        let date = new Date(self.currentDate());
        date.setDate(date.getDate() + 1);
        self.currentDate(date);
        self.generateCalendar();
        self.renderAvailableShifts();
    };
    
    // カレンダー生成
    self.generateCalendar = function() {
        const view = self.currentView();
        
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
        let date = new Date(self.currentDate());
        const year = date.getFullYear();
        const month = date.getMonth();
        
        // 月の最初の日
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        
        // カレンダーの開始日（前月の日付も含む）
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());
        
        const days = [];
        let currentDate = new Date(startDate);
        
        // 6週間分の日付を生成
        for (let i = 0; i < 42; i++) {
            const dayShifts = self.shifts().filter(function(shift) {
                const shiftDate = new Date(shift.shift_date);
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

        self.calendarDays(days);
        self.renderCalendarDays(days);
    };
    
    // 週表示生成
    self.generateWeekView = function() {
        let date = new Date(self.currentDate());
        const startOfWeek = new Date(date);
        startOfWeek.setDate(date.getDate() - date.getDay());
        
        const weekDays = [];
        for (let i = 0; i < 7; i++) {
            const currentDate = new Date(startOfWeek);
            currentDate.setDate(startOfWeek.getDate() + i);
            
            const dayShifts = self.shifts().filter(function(shift) {
                const shiftDate = new Date(shift.shift_date);
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
        let date = new Date(self.currentDate());
        const dayShifts = self.shifts().filter(function(shift) {
            const shiftDate = new Date(shift.shift_date);
            return shiftDate.toDateString() === date.toDateString();
        });
        
        self.renderDayView(date, dayShifts);
    };
    
    // カレンダー日付をレンダリング（テーブル形式）
    self.renderCalendarDays = function(days) {
        const container = document.getElementById('calendar-days-container');

        if (!container) {

            return;
        }
        
        container.innerHTML = '';
        
        // 6週間分の行を作成
        const weeks = [];
        for (let i = 0; i < 6; i++) {
            weeks.push([]);
        }
        
        // 日付を週ごとにグループ化
        days.forEach(function(day, index) {
            const weekIndex = Math.floor(index / 7);
            if (weekIndex < 6) {
                weeks[weekIndex].push(day);
            }
        });
        
        // 各行（週）を作成
        weeks.forEach(function(week) {
            const row = document.createElement('tr');
            
            week.forEach(function(day) {
                const cell = document.createElement('td');
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                
                if (day.date.getMonth() !== self.currentDate().getMonth()) {
                    dayElement.classList.add('other-month');
                }
                
                if (day.isWeekend) {
                    dayElement.classList.add('weekend');
                }
                
                const today = new Date();
                if (day.date.toDateString() === today.toDateString()) {
                    dayElement.classList.add('today');
                }
                
                const dayNumber = document.createElement('div');
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
                        const dateStr = day.date.getFullYear() + '-' + 
                                    String(day.date.getMonth() + 1).padStart(2, '0') + '-' + 
                                    String(day.date.getDate()).padStart(2, '0');
                        window.location.href = '/shifts/create?date=' + dateStr;
                    }
                });
                
                // 日付セルをクリック可能にするスタイル
                dayElement.style.cursor = 'pointer';
                
                // シフトブロックを追加
                day.shifts.forEach(function(shift) {
                    const shiftBlock = document.createElement('div');
                    shiftBlock.className = 'shift-block';
                    if (shift.available_slots === 0) {
                        shiftBlock.classList.add('full');
                    }
                    
                    const timeDiv = document.createElement('div');
                    timeDiv.className = 'shift-time';
                    timeDiv.textContent = shift.start_time + '-' + shift.end_time;
                    shiftBlock.appendChild(timeDiv);
                    
                    const countDiv = document.createElement('div');
                    countDiv.className = 'shift-count';
                    countDiv.textContent = shift.assigned_users.length + '/' + shift.slot_count;
                    shiftBlock.appendChild(countDiv);
                    
                    // クリックイベント
                    shiftBlock.addEventListener('click', function(e) {
                        e.stopPropagation(); // 親要素（日付セル）への伝播を止める
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
        const container = document.getElementById('week-grid-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        // 1行のテーブルを作成
        const row = document.createElement('tr');
        
        weekDays.forEach(function(day) {
            const cell = document.createElement('td');
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            
            if (day.isToday) dayElement.classList.add('today');
            if (day.isWeekend) dayElement.classList.add('weekend');
            
            const dayNumber = document.createElement('div');
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
                    const dateStr = day.date.getFullYear() + '-' + 
                                String(day.date.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(day.date.getDate()).padStart(2, '0');
                    window.location.href = '/shifts/create?date=' + dateStr;
                }
            });
            
            // 日付セルをクリック可能にするスタイル
            dayElement.style.cursor = 'pointer';
            
            // シフトブロックを追加
            day.shifts.forEach(function(shift) {
                const shiftBlock = document.createElement('div');
                shiftBlock.className = 'shift-block';
                if (shift.available_slots === 0) {
                    shiftBlock.classList.add('full');
                }
                
                const timeDiv = document.createElement('div');
                timeDiv.className = 'shift-time';
                timeDiv.textContent = shift.start_time + '-' + shift.end_time;
                shiftBlock.appendChild(timeDiv);
                
                const countDiv = document.createElement('div');
                countDiv.className = 'shift-count';
                countDiv.textContent = shift.assigned_users.length + '/' + shift.slot_count;
                shiftBlock.appendChild(countDiv);
                
                // ツールチップ機能を追加
                shiftBlock.addEventListener('mouseover', function(e) {
                    // ツールチップ要素を作成
                    const tooltip = document.createElement('div');
                    tooltip.className = 'shift-tooltip';
                    tooltip.style.position = 'absolute';
                    tooltip.style.zIndex = '100';
                    tooltip.style.background = 'white';
                    tooltip.style.border = '1px solid #ddd';
                    tooltip.style.padding = '10px';
                    tooltip.style.borderRadius = '4px';
                    tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
                    tooltip.style.minWidth = '200px';

                    // ツールチップの内容を設定
                    tooltip.innerHTML = `
                        <p><strong>時間:</strong> ${shift.start_time.substring(0, 5)} - ${shift.end_time.substring(0, 5)}</p>
                        <p><strong>参加人数:</strong> ${shift.assigned_users.length} / ${shift.slot_count}</p>
                        <p><strong>空き:</strong> ${shift.available_slots}人</p>
                        ${shift.note ? '<p><strong>メモ:</strong> ' + shift.note + '</p>' : ''}
                    `;

                    // ツールチップの位置を調整
                    tooltip.style.top = (e.clientY + 10) + 'px';
                    tooltip.style.left = (e.clientX + 10) + 'px';
                    
                    document.body.appendChild(tooltip);
                });

                shiftBlock.addEventListener('mouseout', function() {
                    // ツールチップを非表示にする
                    const tooltip = document.querySelector('.shift-tooltip');
                    if (tooltip) {
                        document.body.removeChild(tooltip);
                    }
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
        const shiftsContainer = document.getElementById('day-shifts-container');
        
        if (!shiftsContainer) return;
        
        // シフト一覧を更新
        shiftsContainer.innerHTML = '';
        
        if (dayShifts.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 5;
            cell.style.textAlign = 'center';
            cell.style.color = '#999';
            cell.style.padding = '40px';
            cell.textContent = 'この日のシフトはありません';
            row.appendChild(cell);
            shiftsContainer.appendChild(row);
        } else {
            dayShifts.forEach(function(shift) {
                const row = document.createElement('tr');
                row.className = 'day-shift-row';
                if (shift.assigned_users.length >= shift.slot_count) {
                    row.classList.add('full');
                }
                
                // 時間列
                const timeCell = document.createElement('td');
                timeCell.className = 'day-shift-time';
                timeCell.textContent = shift.start_time + '-' + shift.end_time;
                row.appendChild(timeCell);
                
                // シフト情報列
                const infoCell = document.createElement('td');
                infoCell.className = 'day-shift-info';
                let infoHtml = '';
                if (shift.note) {
                    infoHtml += '<div class="day-shift-note">' + shift.note + '</div>';
                }
                infoHtml += '<div class="day-shift-id">ID: ' + shift.id + '</div>';
                infoCell.innerHTML = infoHtml;
                row.appendChild(infoCell);
                
                // 参加者一覧列
                const participantsCell = document.createElement('td');
                participantsCell.className = 'day-shift-participants';
                if (shift.assigned_users.length === 0) {
                    participantsCell.innerHTML = '<span style="color: #999;">参加者なし</span>';
                } else {
                    let participantsHtml = '';
                    shift.assigned_users.forEach(function(user) {
                        participantsHtml += '<div class="participant-item">';
                        participantsHtml += '<div class="participant-name">' + user.name + ' (' + user.status + ')</div>';
                        if (user.self_word && user.self_word.trim() !== '') {
                            participantsHtml += '<div class="participant-comment" style="font-style: italic; color: #666; font-size: 0.9em; margin-top: 2px;">' + user.self_word + '</div>';
                        }
                        participantsHtml += '</div>';
                    });
                    participantsCell.innerHTML = participantsHtml;
                }
                row.appendChild(participantsCell);
                
                // 定員状況列
                const statusCell = document.createElement('td');
                statusCell.className = 'day-shift-status';
                const availableSlots = shift.slot_count - shift.assigned_users.length;
                let statusText = shift.assigned_users.length + '/' + shift.slot_count + '人';
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
                const actionCell = document.createElement('td');
                actionCell.className = 'day-shift-actions';
                
                // 詳細ボタン
                const detailBtn = document.createElement('button');
                detailBtn.className = 'action-btn detail';
                detailBtn.textContent = '詳細';
                detailBtn.addEventListener('click', function() {
                    self.viewShift(shift);
                });
                actionCell.appendChild(detailBtn);
                
                // 編集ボタン（作成者のみ）
                if (shift.created_by === uid) {
                    const editBtn = document.createElement('button');
                    editBtn.className = 'action-btn edit';
                    editBtn.textContent = '編集';
                    editBtn.style.marginLeft = '5px';
                    editBtn.style.backgroundColor = '#28a745';
                    editBtn.addEventListener('click', function() {
                        self.editShift(shift);
                    });
                    actionCell.appendChild(editBtn);
                }
                
                // 参加ボタン（空きがある場合）
                if (availableSlots > 0) {
                    const joinBtn = document.createElement('button');
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
            const view = self.currentView();
            const map = {
                month: { container: 'available-shifts-container',      msg: 'no-shifts-message' },
                week:  { container: 'available-shifts-container-week', msg: 'no-shifts-message-week' },
                day:   { container: 'available-shifts-container-day',  msg: 'no-shifts-message-day' }
            };
            const target = map[view] || map.month;
            self.renderAvailableShiftsForView(view, target.container, target.msg);
        } catch (error) {

        }
    };
    
    // 特定のビュー用の募集中シフトをレンダリング
    self.renderAvailableShiftsForView = function(view, containerId, messageId) {

        const container = document.getElementById(containerId);
        const noShiftsMessage = document.getElementById(messageId);
        
        if (!container) {

            return;
        }

        container.innerHTML = '';
        
        const availableShifts = self.availableShifts();

        // 週表記の場合は、その週のシフトのみをフィルタリング
        let filteredShifts = availableShifts;
        if (view === 'week') {
            const base = new Date(self.currentDate());
            const weekStart = new Date(base);
            weekStart.setHours(0,0,0,0);
            weekStart.setDate(base.getDate() - base.getDay()); // Sun
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6); // Sat
            weekEnd.setHours(23,59,59,999);

            filteredShifts = availableShifts.filter(function(shift) {
                const sd = new Date(shift.shift_date); // ローカル日付
                return sd && sd >= weekStart && sd <= weekEnd;
            });

        }

        // 日表示の場合は、その日のシフトのみをフィルタリング
        if (view === 'day') {
            const cur = new Date(self.currentDate());
            cur.setHours(0,0,0,0);
            const next = new Date(cur);
            next.setDate(cur.getDate() + 1);
            next.setMilliseconds(-1);
            filteredShifts = availableShifts.filter(function(shift) {
                const sd = new Date(shift.shift_date);
                return sd && sd >= cur && sd <= next;
            });
        }
        
        if (filteredShifts.length === 0) {

            if (noShiftsMessage) {
                noShiftsMessage.style.display = 'block';
            }
        } else {

            if (noShiftsMessage) {
                noShiftsMessage.style.display = 'none';
            }
            
            filteredShifts.forEach(function(shift, index) {
                const itemElement = document.createElement('div');
                itemElement.className = 'recruitment-item';
                itemElement.style.cssText = 'cursor: pointer;';
                
                // シフト枠全体のクリックイベント
                itemElement.addEventListener('click', function(e) {
                    // ボタンがクリックされた場合は詳細ページに遷移しない
                    if (e.target.tagName === 'BUTTON') {
                        return;
                    }
                    window.location.href = '/shifts/' + shift.id;
                });
                
                // シフト情報とボタンを横並びにするコンテナ
                const infoContainer = document.createElement('div');
                infoContainer.style.cssText = 'display: flex; justify-content: space-between; align-items: center; width: 100%;';
                
                // 左側のシフト情報
                const shiftInfo = document.createElement('div');
                shiftInfo.style.cssText = 'flex: 1;';
                
                const dateDiv = document.createElement('div');
                dateDiv.className = 'recruitment-date';
                // 秒数表記を削除（HH:MM:SS → HH:MM）
                const startTime = shift.start_time.substring(0, 5);
                const endTime = shift.end_time.substring(0, 5);
                dateDiv.textContent = shift.shift_date + '  ' + startTime + '-' + endTime;
                dateDiv.style.cssText = 'font-weight: bold; margin-bottom: 4px;';
                shiftInfo.appendChild(dateDiv);
                
                const slotsDiv = document.createElement('div');
                slotsDiv.className = 'recruitment-slots';
                slotsDiv.textContent = '空き: ' + shift.available_slots + '人 / 定員: ' + shift.slot_count + '人';
                slotsDiv.style.cssText = 'font-size: 12px; color: #666;';
                shiftInfo.appendChild(slotsDiv);
                
                infoContainer.appendChild(shiftInfo);
                
                // 右側のボタン
                const actionsDiv = document.createElement('div');
                actionsDiv.className = 'recruitment-actions';
                actionsDiv.style.cssText = 'display: flex; gap: 5px; flex-shrink: 0;';
                

        // 参加・取消ボタンはそのまま
                const joinBtn = document.createElement('button');
                joinBtn.className = 'action-btn join';
                joinBtn.textContent = '参加';
                joinBtn.addEventListener('click', function() {
                    self.joinShift(shift);
                });
                actionsDiv.appendChild(joinBtn);
                
                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'action-btn cancel';
                cancelBtn.textContent = '取消';
                cancelBtn.addEventListener('click', function() {
                    self.cancelShift(shift);
                });
                actionsDiv.appendChild(cancelBtn);
                
                infoContainer.appendChild(actionsDiv);
                itemElement.appendChild(infoContainer);
                container.appendChild(itemElement);
            });
        }
    };
    
    // シフト詳細表示
    self.viewShift = function(shift) {
        window.location.href = '/shifts/' + shift.id;
    };
    
    // シフト編集
    self.editShift = function(shift) {
        window.location.href = '/shifts/edit/' + shift.id;
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
            const base = new Date(self.currentDate());

            const y = base.getFullYear(), m = base.getMonth();

            const first = new Date(y, m, 1);
            const last  = new Date(y, m + 1, 0);
            // 前後1週間バッファ
            first.setDate(first.getDate() - 7);
            last.setDate(last.getDate() + 7);
            function fmt(d){ const z=n=>String(n).padStart(2,'0'); return d.getFullYear()+'-'+z(d.getMonth()+1)+'-'+z(d.getDate()); }
            self._from = fmt(first);
            self._to   = fmt(last);

        })();
        const API = window.API_BASE || '/api';
        const uid = window.CURRENT_USER_ID || 0;
        
        $.ajax({
            url: `${API}/shifts`,
            data: { from: self._from, to: self._to, mine: 0, user_id: uid },
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.ok) {

                    // 正規化：型を数値に統一し、available_slots を算出
                    const data = response.data || [];
                    // 配列でない場合は配列に変換
                    if (!Array.isArray(data)) {
                        data = Array.from(data);
                    }
                    const normalized = data.map(function(shift) {
                        const assignedCount = Array.isArray(shift.assigned_users)
                            ? shift.assigned_users.length
                            : Number(shift.assigned_count ?? 0);
                        const slotCount = Number((shift.slot_count ?? shift.capacity ?? 0));
                        const available = (shift.available_slots != null)
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
                    const availableShifts = normalized.filter(function(shift) {

                        return shift.available_slots > 0;
                    });

                    self.availableShifts(availableShifts);

                } else {
                    self.showAlert('シフト一覧の取得に失敗しました: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                self.showAlert('シフト一覧の取得に失敗しました', 'error');

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

        // モーダルダイアログを表示
        self.showCommentModal(shift);

    };
    
    // コメント入力モーダルを表示（緊急ボタン仕様で確実に動作）
    self.showCommentModal = function(shift) {

        // 既存のモーダルをすべて削除
        const existingModals = document.querySelectorAll('#comment-modal, .modal-overlay');
        existingModals.forEach(function(modal) {
            modal.remove();
        });
        
        // 現在のシフトを保存
        self.currentShift = shift;
        
        // グローバルにViewModelを保存
        window.shiftVM = self;
        
        // 緊急ボタン仕様でモーダルを作成
        const modal = document.createElement('div');
        modal.id = 'comment-modal';
        modal.style.cssText = 'position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background: rgba(0,0,0,0.8) !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important;';
        
        modal.innerHTML = `
            <div style="background: white; border-radius: 15px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 0 30px rgba(0,0,0,0.5); position: relative;">
                <button onclick="window.shiftVM.hideCommentModal()" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">×</button>
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 20px;">参加時のひとこと（任意）</h3>
                <textarea id="comment-textarea" rows="4" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; resize: vertical;" placeholder="例：頑張ります！"></textarea>
                <div style="margin-top: 20px; text-align: right;">
                    <button onclick="window.shiftVM.hideCommentModal()" style="background: #ccc; color: #333; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px; font-size: 16px;">キャンセル</button>
                    <button onclick="window.shiftVM.submitJoinShift(window.shiftVM.currentShift, document.getElementById('comment-textarea').value)" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">参加する</button>
                </div>
            </div>
        `;
        
        // モーダルを表示
        document.body.appendChild(modal);

        // テキストエリアにフォーカス
        setTimeout(function() {
            const textarea = document.getElementById('comment-textarea');
            if (textarea) {
                textarea.focus();
            }
        }, 100);
        
        // ESCキーで閉じる
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                self.hideCommentModal();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
        
        // モーダル外クリックで閉じる
        modal.onclick = function(e) {
            if (e.target === modal) {
                self.hideCommentModal();
            }
        };
    };

    // コメントモーダルを非表示
    self.hideCommentModal = function() {

        const modal = document.getElementById('comment-modal');
        if (modal) {
            modal.remove();
        }
    };

    // シフト参加を実際に実行
    self.submitJoinShift = function(shift, comment) {

        // モーダルを閉じる
        self.hideCommentModal();
        
        // 現在のユーザーIDを取得（セッションから）
        const currentUserId = window.CURRENT_USER_ID || 1;
        
        const API = window.API_BASE || '/api';
        fetch(`${API}/shifts/${shift.id}/join`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                shift_id: shift.id,
                user_id: currentUserId,
                status: 'assigned',
                self_word: comment
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.ok) {
                let message = 'シフトに参加しました！';
                if (comment && comment.trim()) {
                    message += '\nコメント: ' + comment;
                }
                alert(message);
                self.loadShifts(); // データを再読み込み
                
                // 詳細ページのデータも更新（詳細ページが開いている場合）
                if (typeof window.refreshShiftDetail === 'function') {
                    window.refreshShiftDetail();
                }
            } else {
                alert('参加に失敗しました: ' + (data.message || 'エラーが発生しました'));
            }
        })
        .catch(function(error) {

            alert('参加に失敗しました: ' + error.message);
        });
    };
    
    // シフト取消
    self.cancelShift = function(shift) {
        if (!confirm('このシフトの参加を取り消しますか？')) {
            return;
        }
        
        const API = window.API_BASE || '/api';
        
        $.ajax({
            url: `${API}/shifts/${shift.id}/cancel`,
            type: 'POST',
            data: {
                csrf_token: 'dummy_token' // 簡易実装
            },
            success: function(response, status, xhr) {
                // レスポンスを手動でJSONパース
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        self.showAlert('シフトの参加を取り消しました', 'success');
                        self.loadShifts();
                        
                        // 詳細ページのデータも更新（詳細ページが開いている場合）
                        if (typeof window.refreshShiftDetail === 'function') {
                            window.refreshShiftDetail();
                        }
                        
                        // 自分のシフトページのデータも更新
                        if (typeof window.refreshMyShifts === 'function') {
                            window.refreshMyShifts();
                        }
                    } else {
                        self.showAlert('シフトの取消に失敗しました: ' + data.message, 'error');
                    }
                } catch (e) {
                    self.showAlert('シフトの取消に失敗しました', 'error');

                }
            },
            error: function(xhr, status, error) {
                const errorMessage = 'シフトの取消に失敗しました';
                
                if (xhr.status === 404) {
                    errorMessage = 'このシフトに参加していません';
                } else if (xhr.status === 409) {
                    errorMessage = 'シフトの取消ができません';
                }
                
                self.showAlert(errorMessage, 'error');

            }
        });
    };
    
    // リスト表示のシフトをレンダリング
    self.renderListShifts = function() {

        const container = document.getElementById('available-shifts-container-list');
        const noShiftsMessage = document.getElementById('no-shifts-message-list');

        if (!container) {

            return;
        }
        
        // コンテナをクリア
        container.innerHTML = '';
        
        // 全てのシフトを取得
        self.loadAllShiftsForList();
    };
    
    // リスト表示用に全てのシフトを取得
    self.loadAllShiftsForList = function() {

        const API = window.API_BASE || '/api';
        const uid = window.CURRENT_USER_ID || 0;
        
        $.ajax({
            url: `${API}/shifts`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                if (response.ok && response.data) {
                    const shifts = response.data;

                    self.renderShiftsList(shifts);
                } else {

                    self.showNoShiftsMessage();
                }
            },
            error: function(xhr, status, error) {

                self.showNoShiftsMessage();
            }
        });
    };
    
    // シフトリストをレンダリング
    self.renderShiftsList = function(shifts) {
        const container = document.getElementById('available-shifts-container-list');
        const noShiftsMessage = document.getElementById('no-shifts-message-list');

        if (!shifts || shifts.length === 0) {

            self.showNoShiftsMessage();
            return;
        }

        // シフトをリスト形式で表示
        shifts.forEach(function(shift) {
            const shiftItem = document.createElement('div');
            shiftItem.className = 'shift-list-item';
            
            const shiftInfo = document.createElement('div');
            shiftInfo.className = 'shift-list-info';
            
            const title = document.createElement('div');
            title.className = 'shift-list-title';
            title.textContent = shift.title || 'シフト';
            
            const details = document.createElement('div');
            details.className = 'shift-list-details';
            details.textContent = shift.shift_date + ' ' + shift.start_time + '〜' + shift.end_time;
            
            // 参加人数を表示
            const status = document.createElement('div');
            status.className = 'shift-list-status';
            const joinedCount = shift.assigned_users ? shift.assigned_users.length : 0;
            const slotCount = shift.slot_count || 0;
            const availableSlots = slotCount - joinedCount;
            let statusText = joinedCount + '/' + slotCount + '人';
            
            if (availableSlots === 0) {
                statusText += ' (満員)';
                status.style.color = '#d32f2f';
                status.style.fontWeight = 'bold';
            } else {
                statusText += ' (空き: ' + availableSlots + '人)';
                status.style.color = '#2e7d32';
            }
            status.textContent = statusText;
            
            shiftInfo.appendChild(title);
            shiftInfo.appendChild(details);
            shiftInfo.appendChild(status);
            
            const actions = document.createElement('div');
            actions.className = 'shift-list-actions';
            
            const viewBtn = document.createElement('button');
            viewBtn.className = 'shift-list-btn primary';
            viewBtn.textContent = '詳細';
            viewBtn.onclick = function() {
                window.location.href = '/shifts/' + shift.id;
            };
            
            const joinBtn = document.createElement('button');
            joinBtn.className = 'shift-list-btn secondary';
            joinBtn.textContent = '参加';
            joinBtn.onclick = function() {
                self.joinShift(shift);
            };
            
            actions.appendChild(viewBtn);
            actions.appendChild(joinBtn);
            
            shiftItem.appendChild(shiftInfo);
            shiftItem.appendChild(actions);
            
            container.appendChild(shiftItem);

        });

        // リストビューの表示状態を確認
        const listView = document.querySelector('.list-recruitment-section');
        if (listView) {

        }
        
        // コンテナの表示状態を確認

        if (noShiftsMessage) {
            noShiftsMessage.style.display = 'none';
        }
    };
    
    // シフトなしメッセージを表示
    self.showNoShiftsMessage = function() {
        const noShiftsMessage = document.getElementById('no-shifts-message-list');
        if (noShiftsMessage) {
            noShiftsMessage.style.display = 'block';
        }
    };
    
    // 初期化
    self.setView('month'); // 初期表示を月表示に設定
    self.generateCalendar(); // カレンダーを先に生成
    self.loadShifts();
    
    // グローバルな更新関数を登録
    window.refreshShiftList = function() {

        self.loadShifts();
    };
    
    // 手動でナビゲーションボタンのイベントリスナーを追加
    setTimeout(function() {
        const prevBtn = document.querySelector('.nav-btn[data-bind*="previousMonth"]');
        const nextBtn = document.querySelector('.nav-btn[data-bind*="nextMonth"]');
        
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

// グローバルデバッグ関数
window.debugModal = function() {

    var modal1 = document.getElementById('comment-modal');
    if (modal1) {

    }
    
    var modal2 = document.getElementById('comment-modal-view');
    if (modal2) {

    }

};

// Knockout.jsのバインディングを適用
document.addEventListener('DOMContentLoaded', function() {

    try {
        const viewModel = new ShiftViewModel();
        
        // グローバルに保存（デバッグとフォールバック用）
        window.shiftViewModel = viewModel;

        ko.applyBindings(viewModel);

        // バインディング後にボタンの状態を再確認
        setTimeout(function() {
            const uid = Number(window.CURRENT_USER_ID || document.querySelector('meta[name="current-user-id"]')?.content || 0);

            if (uid) {

                const btns = document.querySelectorAll('.action-btn.btn-participate, .action-btn.btn-cancel, .btn-join, .btn-cancel-shift, .btn-add-shift, .btn.my-shifts-btn, .view-btn');

                btns.forEach(b => { 
                    b.disabled = false; 
                    b.title = ''; 
                });
                
                // 月切り替えボタン（.nav-btn）は有効化しない
                // Knockout.jsのバインディングだけを使用
            }
        }, 100);
        
    } catch (error) {

    }
});
