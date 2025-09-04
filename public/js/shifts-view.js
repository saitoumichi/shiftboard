// シフト詳細ページ用JavaScript

// 1) KO の互換（古い Knockout の場合）
if (window.ko && typeof ko.pureComputed !== 'function') {
  ko.pureComputed = ko.computed;
}

// 1.1) KO のユーティリティ互換
if (window.ko && typeof ko.toJS !== 'function') {
  ko.toJS = function (value) {
    try {
      // observable の中身を取り出すだけの簡易版
      return (typeof value === 'function') ? value() : value;
    } catch (e) {
      return value;
    }
  };
}
if (window.ko && typeof ko.toJSON !== 'function') {
  ko.toJSON = function (value, replacer, space) {
    try {
      var v = (typeof ko.toJS === 'function') ? ko.toJS(value) : value;
      return JSON.stringify(v, replacer || null, space || 2);
    } catch (e) {
      try { return JSON.stringify(value); } catch (_) { return String(value); }
    }
  };
}

var defaultShift = {
    id: null, 
    shift_date: '', 
    start_time: '', 
    end_time: '',
    note: '', 
    slot_count: 1, 
    available_slots: 0, 
    assigned_users: []
};

// Knockout.js ViewModel
function ShiftDetailViewModel() {
    var vm = this;
    
    // データ
    vm.shift = ko.observable(null);
    vm.participants = ko.observableArray([]);
    vm.loading = ko.observable(true);
    vm.error = ko.observable('');
    vm.showRecruitmentDetails = ko.observable(false);
    
    // 計算プロパティ
    vm.shiftTitle = ko.pureComputed(function() {
        var s = vm.shift();
        if (!s) return '';
        var t = (s.start_time && s.end_time) ? (s.start_time.substring(0,5) + '〜' + s.end_time.substring(0,5)) : '';
        return (s.shift_date ? s.shift_date + ' ' : '') + t;
    });
    
    vm.shiftDate = ko.pureComputed(function() {
        var s = vm.shift();
        return s ? s.shift_date : '';
    });
    
    vm.shiftTime = ko.pureComputed(function() {
        var s = vm.shift();
        return (s && s.start_time && s.end_time) ? (s.start_time.substring(0,5) + '〜' + s.end_time.substring(0,5)) : '';
    });
    
    vm.shiftNote = ko.pureComputed(function() {
        var s = vm.shift();
        return s ? (s.note || '備考なし') : '';
    });
    
    vm.slotInfo = ko.pureComputed(function() {
        var s = vm.shift();
        if (!s) return '';
        var assigned = s.assigned_users ? s.assigned_users.length : 0;
        var total = s.slot_count || 0;
        return assigned + '/' + total + '人';
    });
    
    // デバッグ用 JSON（テンプレから <pre data-bind="text: debugJSON"> で参照）
    vm.debugJSON = ko.pureComputed(function () {
        var s = vm.shift();
        try {
            return ko.toJSON(s, null, 2);
        } catch (e) {
            try { return JSON.stringify(s, null, 2); } catch (_) { return String(s); }
        }
    });
    
    // 現在のシフトを再読込
    vm.loadShiftDetail = function () {
        var s = vm.shift && vm.shift();
        if (s && s.id) {
            vm.load(s.id);
        }
    };
    
    // アラート表示
    vm.showAlert = function(message, type) {
        var alert = document.createElement('div');
        alert.className = 'alert alert-' + type;
        alert.textContent = message;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 10px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;';
        document.body.appendChild(alert);
        setTimeout(function() {
            alert.style.display = 'none';
        }, 5000);
    };
    
    // シフト詳細を取得
    vm.load = function(id) {
        var self = this; // 正しいスコープを確保
        console.log('Load function called with ID:', id);
        self.loading(true);
        self.error('');
        
        var firstTriedUrl = apiUrlFor(id);
        var fallbackTriedUrl = apiFallbackUrlFor(id);

        function doFetch(url) {
          return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) {
              if (!r.ok) {
                var err = new Error('HTTP ' + r.status);
                err.status = r.status;
                err.url = url;
                throw err;
              }
              return r.json();
            });
        }

        // まず /api/shifts/:id を試し、失敗したら /api/v1/shifts/:id にフォールバック
        return doFetch(firstTriedUrl)
          .catch(function (e) {
            console.warn('Primary API failed, trying fallback:', e && e.status, e && e.url);
            return doFetch(fallbackTriedUrl);
          })
          .then(function (json) {
            var payload = json && (json.data || json);
            if (!payload || !payload.id) throw new Error('Invalid payload');
            console.log('Setting shift data:', payload);
            self.shift(payload);

            self.participants.removeAll();
            if (payload.assigned_users && Array.isArray(payload.assigned_users)) {
              payload.assigned_users.forEach(function (user) { self.participants.push(user); });
            }
            console.log('Data loaded successfully');
          })
          .catch(function (e) {
            console.error('Load failed:', e);
            self.error('シフトの取得に失敗しました（' + (e && (e.status || e.message) || 'unknown') + '）。');
            self.shift(defaultShift);
          })
          .finally(function () {
            console.log('Finally block - setting loading to false');
            console.log('Shift in finally:', self.shift());
            console.log('Loading before setting false:', self.loading());
            self.loading(false);
            console.log('Loading after setting false:', self.loading());
            console.log('Shift after finally:', self.shift());
          });
    };
    
    // 募集中の時刻を表示
    vm.showRecruitmentTimes = function() {
        vm.showRecruitmentDetails(!vm.showRecruitmentDetails());
    };
    
    // 参加/取消の切り替え
    vm.toggleParticipation = function() {
        var shift = vm.shift();
        if (!shift) return;
        
        // 既に参加しているかチェック
        var isParticipating = vm.participants().some(function(p) {
            return p.user_id === 1; // 仮のユーザーID
        });
        
        if (isParticipating) {
            vm.cancelParticipation(shift.id);
        } else {
            vm.joinShift(shift.id);
        }
    };
    
    // シフト参加
    vm.joinShift = function(shiftId) {
        $.ajax({
            url: '/api/shifts/' + shiftId + '/join',
            type: 'POST',
            data: {
                csrf_token: 'dummy_token'
            },
            success: function(response) {
                if (response.success) {
                    vm.showAlert('シフトに参加しました！自分のシフトページで確認できます。', 'success');
                    vm.loadShiftDetail(); // データを再読み込み
                    
                    // 自分のシフトページのデータも更新（グローバル関数があれば）
                    if (typeof window.refreshMyShifts === 'function') {
                        window.refreshMyShifts();
                    }
                } else {
                    vm.showAlert('参加に失敗しました: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = '参加に失敗しました';
                if (xhr.status === 409) {
                    errorMessage = '既に参加しているか、定員に達しています';
                }
                vm.showAlert(errorMessage, 'error');
            }
        });
    };
    
    // シフト参加を取消
    vm.cancelParticipation = function(shiftId) {
        $.ajax({
            url: '/api/shifts/' + shiftId + '/cancel',
            type: 'POST',
            data: {
                csrf_token: 'dummy_token'
            },
            success: function(response) {
                if (response.success) {
                    vm.showAlert('シフト参加を取消しました', 'success');
                    vm.loadShiftDetail(); // データを再読み込み
                    
                    // 自分のシフトページのデータも更新（グローバル関数があれば）
                    if (typeof window.refreshMyShifts === 'function') {
                        window.refreshMyShifts();
                    }
                } else {
                    vm.showAlert('取消に失敗しました: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                vm.showAlert('取消に失敗しました', 'error');
            }
        });
    };
    
    // シフト編集
    vm.editShift = function() {
        var shift = vm.shift();
        if (!shift) return;
        
        // 編集ページへ遷移
        window.location.href = '/shifts/' + shift.id + '/edit';
    };
    
    // 戻るボタン
    vm.goBack = function() {
        window.history.back();
    };
    
}

// 2) シングルトンな VM を使う
window.__shiftVM = window.__shiftVM || new ShiftDetailViewModel();
var vm = window.__shiftVM;

// ===== Helper: Resolve Shift ID from multiple URL patterns or data attribute =====
function resolveShiftId() {
  // 1) data-id on root: <div id="shift-detail-root" data-shift-id="123">
  var rootEl = document.getElementById('shift-detail-root');
  var dataId = rootEl && rootEl.getAttribute('data-shift-id');
  if (dataId && /^\d+$/.test(dataId)) return dataId;

  // 2) /shifts/123 or /shifts/view/123 or /shift/123
  var m = location.pathname.match(/\/shifts?(?:\/view)?\/(\d+)\b/);
  if (m) return m[1];

  // 3) query param ?id=123
  var sp = new URLSearchParams(location.search);
  var qid = sp.get('id');
  if (qid && /^\d+$/.test(qid)) return qid;

  return null;
}

// ===== Helper: API URL builder with fallback (/api/shifts/:id or /api/v1/shifts/:id) =====
var SHIFT_API_BASE = (window.SHIFT_API_BASE || '/api/shifts/');
var SHIFT_API_FALLBACK = (window.SHIFT_API_FALLBACK || '/api/v1/shifts/');
function apiUrlFor(id) {
  return SHIFT_API_BASE.replace(/\/+$/,'/') + encodeURIComponent(id);
}
function apiFallbackUrlFor(id) {
  return SHIFT_API_FALLBACK.replace(/\/+$/,'/') + encodeURIComponent(id);
}

// 3) すでにバインド済みなら再バインドしない
(function bindOnce() {
  var root = document.getElementById('shift-detail-root') || document.body;
  function bind() {
    try {
      // dataFor が無い古い KO でも、原則 apply は一度だけ呼ぶ構成に
      if (window.ko && ko.dataFor && ko.dataFor(root)) {
        console.log('Knockout already bound, skip rebind');
        return;
      }
      ko.applyBindings(vm, root);
      console.log('Knockout binding applied ONCE');
    } catch (e) {
      console.error('Binding error:', e);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();

// 4) ID を取って "同じ vm" にロードする
(function initLoad() {
  var id = resolveShiftId();
  if (!id) {
    vm.loading(false);
    vm.error && vm.error('シフトIDがURLから取得できませんでした。');
    return;
  }
  console.log('Loading shift detail for ID:', id);
  // ここで new し直さないこと！！ vm.load を同じインスタンスに対して呼ぶ
  vm.load(id);
})();