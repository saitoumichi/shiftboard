// 自分のシフトページ用JavaScript

// Knockout.js ViewModel
function MyShiftsViewModel() {
    var self = this;
    
    // データ
    self.shifts = ko.observableArray([]);
    self.loading = ko.observable(false);
    self.startDate = ko.observable('');
    self.endDate = ko.observable('');
    self.totalShifts = ko.observable(0);
    self.totalHours = ko.observable(0);
    self.averageHoursPerDay = ko.observable(0);
    
    // ヘルパー関数
    self.displayDateTime = function(shift) {
        if (!shift || typeof shift !== 'object') return '';
        
        var date = shift.shift_date || '';
        var startTime = shift.start_time || '';
        var endTime = shift.end_time || '';
        
        // 日付を読みやすい形式に変換
        if (date && date.length >= 10) {
            var year = date.substring(0, 4);
            var month = date.substring(5, 7);
            var day = date.substring(8, 10);
            month = parseInt(month, 10).toString();
            day = parseInt(day, 10).toString();
            date = year + '年' + month + '月' + day + '日';
        }
        
        // 時間を読みやすい形式に変換
        if (startTime && startTime.length > 5) {
            startTime = startTime.substring(0, 5);
        }
        if (endTime && endTime.length > 5) {
            endTime = endTime.substring(0, 5);
        }
        
        return date + ' ' + startTime + '-' + endTime;
    };
    
    self.slotInfo = function(shift) {
        if (!shift || typeof shift !== 'object') return '';
        
        var assigned = shift.assigned_count || 0;
        var total = shift.slot_count || 1;
        return assigned + '/' + total;
    };
    
    // 期間設定
    self.setThisWeek = function() {
        self.loading(true); // ローディング開始
        
        var today = new Date();
        var startOfWeek = new Date(today);
        startOfWeek.setDate(today.getDate() - today.getDay());
        var endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        self.startDate(startOfWeek.toISOString().split('T')[0]);
        self.endDate(endOfWeek.toISOString().split('T')[0]);
        self.loadMyShifts();
    };
    
    self.setThisMonth = function() {
        console.log('setThisMonth called - setting loading to true');
        self.loading(true); // ローディング開始
        console.log('Loading state after setThisMonth:', self.loading());
        
        // 手動でローディング表示を更新
        var loadingDiv = document.querySelector('.loading');
        var shiftsDiv = document.querySelector('[data-bind="visible: !loading()"]');
        
        if (loadingDiv) {
            loadingDiv.style.display = 'block';
            console.log('Loading div shown manually');
        }
        if (shiftsDiv) {
            shiftsDiv.style.display = 'none';
            console.log('Shifts div hidden manually');
        }
        
        var today = new Date();
        var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        var endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        self.startDate(startOfMonth.toISOString().split('T')[0]);
        self.endDate(endOfMonth.toISOString().split('T')[0]);
        self.loadMyShifts();
    };
    
    self.setNextMonth = function() {
        self.loading(true); // ローディング開始
        
        var today = new Date();
        var nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
        var endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
        
        self.startDate(nextMonth.toISOString().split('T')[0]);
        self.endDate(endOfNextMonth.toISOString().split('T')[0]);
        self.loadMyShifts();
    };
    
    // 自分のシフトを取得
    self.loadMyShifts = function() {
        self.loading(true);
        
        var startDate = self.startDate();
        var endDate = self.endDate();
        
        if (!startDate || !endDate) {
            self.loading(false); // ローディングを停止
            self.setThisMonth();
            return;
        }
        
        $.ajax({
            url: '/api/my/shifts',
            type: 'GET',
            data: {
                start: startDate,
                end: endDate
            },
            dataType: 'json',
            success: function(response) {
                console.log('API Success:', response);
                if (response.success) {
                    // データを整形
                    var formattedShifts = response.data.map(function(shift) {
                        return {
                            id: shift.id,
                            shift_date: shift.shift_date,
                            start_time: shift.start_time,
                            end_time: shift.end_time,
                            slot_count: shift.slot_count,
                            assigned_count: shift.assigned_count || 0,
                            note: shift.note || ''
                        };
                    });
                    console.log('Formatted shifts:', formattedShifts);
                    self.shifts(formattedShifts);
                    self.calculateStats(formattedShifts);
                    
                    // 手動でシフト一覧を更新
                    setTimeout(function() {
                        var shiftsContainer = document.querySelector('[data-bind="visible: !loading()"]');
                        if (shiftsContainer && formattedShifts.length > 0) {
                            // シフト一覧のHTMLを手動で生成
                            var shiftsHTML = '';
                            formattedShifts.forEach(function(shift) {
                                shiftsHTML += '<div class="shift-item">';
                                shiftsHTML += '<div class="shift-date-time">' + self.displayDateTime(shift) + '</div>';
                                shiftsHTML += '<div class="shift-slot-info">' + self.slotInfo(shift) + '</div>';
                                shiftsHTML += '<div class="shift-note" style="margin-top: 8px; color: #666;">' + (shift.note || '') + '</div>';
                                shiftsHTML += '</div>';
                            });
                            shiftsContainer.innerHTML = shiftsHTML;
                            console.log('Shifts HTML updated manually');
                        }
                    }, 100);
                } else {
                    self.showAlert('シフト一覧の取得に失敗しました: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                self.showAlert('シフト一覧の取得に失敗しました', 'error');
                console.error('Error:', error);
            },
            complete: function() {
                console.log('AJAX Complete - setting loading to false');
                self.loading(false);
                console.log('Loading state after complete:', self.loading());
                
                // 手動でDOMを更新
                setTimeout(function() {
                    var loadingDiv = document.querySelector('.loading');
                    var shiftsDiv = document.querySelector('[data-bind="visible: !loading()"]');
                    
                    if (loadingDiv) {
                        loadingDiv.style.display = 'none';
                        console.log('Loading div hidden manually');
                    }
                    if (shiftsDiv) {
                        shiftsDiv.style.display = 'block';
                        console.log('Shifts div shown manually');
                        
                        // シフトデータが存在する場合は手動で表示
                        var shifts = self.shifts();
                        console.log('Current shifts in complete:', shifts);
                        if (shifts && shifts.length > 0) {
                            var shiftsHTML = '';
                            shifts.forEach(function(shift) {
                                shiftsHTML += '<div class="shift-item">';
                                shiftsHTML += '<div class="shift-date-time">' + self.displayDateTime(shift) + '</div>';
                                shiftsHTML += '<div class="shift-slot-info">' + self.slotInfo(shift) + '</div>';
                                shiftsHTML += '<div class="shift-note" style="margin-top: 8px; color: #666;">' + (shift.note || '') + '</div>';
                                shiftsHTML += '</div>';
                            });
                            shiftsDiv.innerHTML = shiftsHTML;
                            console.log('Shifts HTML updated in complete callback');
                        }
                    }
                }, 100);
            }
        });
    };
    
    // 統計情報を計算
    self.calculateStats = function(shifts) {
        var totalShifts = shifts.length;
        var totalHours = 0;
        
        shifts.forEach(function(shift) {
            var startTime = shift.start_time;
            var endTime = shift.end_time;
            
            if (startTime && endTime) {
                var start = new Date('1970-01-01T' + startTime);
                var end = new Date('1970-01-01T' + endTime);
                var diffMs = end - start;
                var diffHours = diffMs / (1000 * 60 * 60);
                totalHours += diffHours;
            }
        });
        
        var averageHoursPerDay = totalShifts > 0 ? (totalHours / totalShifts).toFixed(1) : 0;
        
        self.totalShifts(totalShifts);
        self.totalHours(Math.round(totalHours));
        self.averageHoursPerDay(averageHoursPerDay);
    };
    
    // アラート表示
    self.showAlert = function(message, type) {
        // アラート表示の実装
        alert(message);
    };
    
    // CSV出力
    self.exportCSV = function() {
        var shifts = self.shifts();
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
                         (shift.assigned_count || 0) + '/' + shift.slot_count + ',' + 
                         (shift.note || '') + '\n';
        });
        
        // ファイルダウンロード
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'my_shifts_' + new Date().toISOString().split('T')[0] + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        self.showAlert('CSVファイルを出力しました', 'success');
    };
    
    // 戻る
    self.goBack = function() {
        window.location.href = '/shifts';
    };
    
    // 初期化
    console.log('Initializing MyShiftsViewModel');
    self.shifts([]); // 空の配列で初期化
    console.log('Initial loading state:', self.loading());
    self.setThisMonth();
}

// グローバル更新関数を定義
window.refreshMyShifts = function() {
    if (window.myShiftsViewModel) {
        window.myShiftsViewModel.loadMyShifts();
    }
};

// ページ読み込み時にViewModelを適用
$(document).ready(function() {
    try {
        window.myShiftsViewModel = new MyShiftsViewModel();
        ko.applyBindings(window.myShiftsViewModel);
        console.log('MyShiftsViewModel applied successfully');
    } catch (error) {
        console.error('Knockout.js binding error:', error);
    }
});
