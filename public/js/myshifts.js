// 自分のシフトページ用JavaScript

// 参照専用。再代入・再宣言しない
var uid = window.CURRENT_USER_ID; // 使わなければ削ってOK

// API_BASEとCURRENT_USER_IDはビューファイルで設定済み

// 未ログインガード
if (!window.CURRENT_USER_ID) {
  alert('ログインが必要です');
  location.href = '/';
}

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

  // ヘルパー
  self.displayDateTime = function (shift) {
    if (!shift || typeof shift !== 'object') return '';

    var date = shift.shift_date || '';
    var startTime = shift.start_time || '';
    var endTime = shift.end_time || '';

    if (date && date.length >= 10) {
      var year = date.substring(0, 4);
      var month = parseInt(date.substring(5, 7), 10).toString();
      var day = parseInt(date.substring(8, 10), 10).toString();
      date = year + '年' + month + '月' + day + '日';
    }
    if (startTime && startTime.length > 5) startTime = startTime.substring(0, 5);
    if (endTime && endTime.length > 5) endTime = endTime.substring(0, 5);

    return date + ' ' + startTime + '-' + endTime;
  };

  self.slotInfo = function (shift) {
    if (!shift || typeof shift !== 'object') return '';
    var assigned = shift.assigned_count || 0;
    var total = shift.slot_count || 1;
    return assigned + '/' + total;
  };

  // 期間設定
  self.setThisWeek = function () {
    self.loading(true);
    var today = new Date();
    var startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay());
    var endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);

    self.startDate(startOfWeek.toISOString().split('T')[0]);
    self.endDate(endOfWeek.toISOString().split('T')[0]);
    self.loadMyShifts();
  };

  self.setThisMonth = function () {
    self.loading(true);

    var today = new Date();
    var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    var endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    self.startDate(startOfMonth.toISOString().split('T')[0]);
    self.endDate(endOfMonth.toISOString().split('T')[0]);
    self.loadMyShifts();
  };

  self.setNextMonth = function () {
    self.loading(true);

    var today = new Date();
    var nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    var endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);

    self.startDate(nextMonth.toISOString().split('T')[0]);
    self.endDate(endOfNextMonth.toISOString().split('T')[0]);
    self.loadMyShifts();
  };

  // 自分のシフトを取得
  self.loadMyShifts = function () {
    self.loading(true);

    var startDate = self.startDate();
    var endDate = self.endDate();

    if (!startDate || !endDate) {
      self.loading(false);
      self.setThisMonth();
      return;
    }

    // 一覧はGETでAPIへ
    const API = window.API_BASE || '/api';
    const uid = window.CURRENT_USER_ID || 0;
    
    return $.getJSON(`${API}/shifts`, {
      start: startDate,
      end: endDate,
      mine: 1,
      user_id: uid
    })
      .done(function (response) {
        console.log('API Success:', response);
        if (response.success) {
          var formattedShifts = response.data.map(function (shift) {
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
          self.showAlert('シフト一覧の取得に失敗しました: ' + (response.message || ''), 'error');
        }
      })
      .fail(function (xhr, status, error) {
        console.error('Error:', error);
        self.showAlert('シフト一覧の取得に失敗しました', 'error');
      })
      .always(function () {
        self.loading(false);
      });
  };

  // 統計
  self.calculateStats = function (shifts) {
    var totalShifts = shifts.length;
    var totalHours = 0;

    shifts.forEach(function (shift) {
      var startTime = shift.start_time;
      var endTime = shift.end_time;
      if (startTime && endTime) {
        var start = new Date('1970-01-01T' + startTime);
        var end = new Date('1970-01-01T' + endTime);
        totalHours += (end - start) / (1000 * 60 * 60);
      }
    });

    var averageHoursPerDay = totalShifts > 0 ? (totalHours / totalShifts).toFixed(1) : 0;

    self.totalShifts(totalShifts);
    self.totalHours(Math.round(totalHours));
    self.averageHoursPerDay(averageHoursPerDay);
  };

  // アラート
  self.showAlert = function (message, type) {
    alert(message);
  };

  // CSV
  self.exportCSV = function () {
    var shifts = self.shifts();
    if (shifts.length === 0) {
      self.showAlert('出力するシフトがありません', 'error');
      return;
    }
    var csvContent = '日付,開始時間,終了時間,参加者数/総枠数,備考\n';
    shifts.forEach(function (shift) {
      csvContent +=
        shift.shift_date + ',' +
        shift.start_time + ',' +
        shift.end_time + ',' +
        (shift.assigned_count || 0) + '/' + shift.slot_count + ',' +
        (shift.note || '') + '\n';
    });
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    var url = URL.createObjectURL(blob);
    link.href = url;
    link.download = 'my_shifts_' + new Date().toISOString().split('T')[0] + '.csv';
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    self.showAlert('CSVファイルを出力しました', 'success');
  };

  // 戻る
  self.goBack = function () {
    window.location.href = '/shifts';
  };

  // 初期化
  self.shifts([]);
  self.setThisMonth();
} // ←←← ここで ViewModel 定義を閉じるのがポイント！

// ====== グローバル関数とバインドは関数の外に置く ======
window.refreshMyShifts = function () {
  if (window.myShiftsViewModel) {
    window.myShiftsViewModel.loadMyShifts();
  }
};

$(document).ready(function () {
  try {
    // すでに applyBindings 済みで二重適用しないようガード
    if (!window.myShiftsViewModel) {
      window.myShiftsViewModel = new MyShiftsViewModel();
      ko.applyBindings(window.myShiftsViewModel);
      console.log('MyShiftsViewModel applied');
    }
  } catch (e) {
    console.error('Knockout.js binding error:', e);
  }
});