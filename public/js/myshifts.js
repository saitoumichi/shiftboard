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
    
    // 計算プロパティ
    self.displayDateTime = ko.computed(function() {
        return function(shift) {
            var date = shift.shift_date;
            var startTime = shift.start_time;
            var endTime = shift.end_time;
            
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
    });
    
    self.slotInfo = ko.computed(function() {
        return function(shift) {
            var assigned = shift.assigned_count || 0;
            var total = shift.slot_count || 1;
            return assigned + '/' + total;
        };
    });
    
    // 期間設定
    self.setThisWeek = function() {
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
        var today = new Date();
        var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        var endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        self.startDate(startOfMonth.toISOString().split('T')[0]);
        self.endDate(endOfMonth.toISOString().split('T')[0]);
        self.loadMyShifts();
    };
    
    self.setNextMonth = function() {
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
                    self.shifts(formattedShifts);
                    self.calculateStats(formattedShifts);
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
