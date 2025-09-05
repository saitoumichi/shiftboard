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
      vm.isReady = ko.observable(false);
      
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
      
      // 参加状況の判定
      vm.isParticipating = ko.pureComputed(function() {
          var s = vm.shift();
          if (!s || !s.assigned_users) return false;
          // 現在のユーザーID（デバッグ用に1を固定）
          var currentUserId = 1;
          return s.assigned_users.some(function(user) {
              return user.id === currentUserId || user.name === 'Alice'; // デバッグ用
          });
      });
      
      vm.canParticipate = ko.pureComputed(function() {
          var s = vm.shift();
          if (!s) return false;
          var assigned = s.assigned_users ? s.assigned_users.length : 0;
          return assigned < s.slot_count;
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
          var self = this;
          console.log('Load function called with ID:', id);
          self.loading(true);
          self.error('');
          self.isReady(false);
          
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
  
          return doFetch(firstTriedUrl)
            .catch(function (e) {
              console.warn('Primary API failed, trying fallback:', e && e.status, e && e.url);
              return doFetch(fallbackTriedUrl);
            })
            .then(function (json) {
              console.log('API Response:', json);
              var payload = json && (json.data || json);
              if (!payload || !payload.id) throw new Error('Invalid payload');
              console.log('Setting shift data:', payload);
              
              self.participants.removeAll();
              if (payload.assigned_users && Array.isArray(payload.assigned_users)) {
                payload.assigned_users.forEach(function (user) { self.participants.push(user); });
              }
  
              self.shift(payload);
              self.loading(false);
              self.isReady(true);
              
              console.log('Data loaded successfully');
              console.log('isReady after load:', self.isReady());
              console.log('shift after load:', self.shift());
              
              // デバッグ情報の手動更新（バインディング再適用は不要）
              setTimeout(function() {
                console.log('Final state - loading:', self.loading());
                console.log('Final state - isReady:', self.isReady());
                console.log('Final state - shift:', self.shift());
                
                var root = document.getElementById('shift-detail-root') || document.body;
                
                // 手動でデバッグ情報を更新
                var debugDiv = root.querySelector('[style*="background: yellow"]');
                if (debugDiv) {
                  var loadingSpan = debugDiv.querySelector('span[data-bind*="loading"]');
                  var isReadySpan = debugDiv.querySelector('span[data-bind*="isReady"]');
                  var shiftSpan = debugDiv.querySelector('span[data-bind*="Shiftデータ"]');
                  var shiftIdSpan = debugDiv.querySelector('span[data-bind*="Shift ID"]');
                  var shiftDateSpan = debugDiv.querySelector('span[data-bind*="Shift日付"]');
                  
                  if (loadingSpan) loadingSpan.textContent = self.loading() ? 'true' : 'false';
                  if (isReadySpan) isReadySpan.textContent = self.isReady() ? 'true' : 'false';
                  if (shiftSpan) shiftSpan.textContent = self.shift() ? 'あり' : 'なし';
                  if (shiftIdSpan) shiftIdSpan.textContent = self.shift() && self.shift().id || 'なし';
                  if (shiftDateSpan) shiftDateSpan.textContent = self.shift() && self.shift().shift_date || 'なし';
                  
                  console.log('Debug info updated manually');
                }
                
                // アクションボタンエリアのデバッグ情報も手動更新
                var actionDebugDiv = root.querySelector('[style*="background: #f0f0f0"]');
                if (actionDebugDiv) {
                  var isParticipatingSpan = actionDebugDiv.querySelector('span[data-bind*="isParticipating"]');
                  var canParticipateSpan = actionDebugDiv.querySelector('span[data-bind*="canParticipate"]');
                  var shiftExistsSpan = actionDebugDiv.querySelector('span[data-bind*="shift存在"]');
                  var participantsCountSpan = actionDebugDiv.querySelector('span[data-bind*="参加者数"]');
                  var slotCountSpan = actionDebugDiv.querySelector('span[data-bind*="定員"]');
                  
                  // 手動で値を計算
                  var shift = self.shift();
                  var isParticipating = false;
                  var canParticipate = false;
                  
                  if (shift) {
                      // 参加状況の判定（デバッグ用に1を固定）
                      var currentUserId = 1;
                      isParticipating = shift.assigned_users && shift.assigned_users.some(function(user) {
                          return user.id === currentUserId || user.name === 'Alice';
                      });
                      
                      // 参加可能かの判定
                      var assigned = shift.assigned_users ? shift.assigned_users.length : 0;
                      canParticipate = assigned < shift.slot_count;
                  }
                  
                  if (isParticipatingSpan) isParticipatingSpan.textContent = isParticipating ? 'true' : 'false';
                  if (canParticipateSpan) canParticipateSpan.textContent = canParticipate ? 'true' : 'false';
                  if (shiftExistsSpan) shiftExistsSpan.textContent = shift ? 'あり' : 'なし';
                  if (participantsCountSpan) participantsCountSpan.textContent = shift ? shift.assigned_users.length : 0;
                  if (slotCountSpan) slotCountSpan.textContent = shift ? shift.slot_count : 0;
                  
                  console.log('Action debug info updated manually');
                  
                  // アクションボタンの表示を手動で制御
                  var participateBtn = root.querySelector('.btn-participate');
                  var cancelBtn = root.querySelector('.btn-cancel');
                  var capacityMessage = root.querySelector('.action-message');
                  
                  if (participateBtn) {
                    participateBtn.style.display = (!isParticipating && canParticipate) ? 'block' : 'none';
                    // クリックイベントを追加
                    participateBtn.onclick = function() {
                      console.log('Participate button clicked!');
                      self.joinShift(shift.id);
                    };
                  }
                  if (cancelBtn) {
                    cancelBtn.style.display = isParticipating ? 'block' : 'none';
                  }
                  if (capacityMessage) {
                    capacityMessage.style.display = (!canParticipate && !isParticipating) ? 'block' : 'none';
                  }
                  
                  console.log('Action buttons visibility updated manually');
                }
                
                // シフト詳細情報も手動で更新
                var shiftTitleElement = root.querySelector('[data-bind*="shiftTitle"]');
                var shiftDateElement = root.querySelector('[data-bind*="shiftDate"]');
                var shiftTimeElement = root.querySelector('[data-bind*="shiftTime"]');
                var shiftNoteElement = root.querySelector('[data-bind*="shiftNote"]');
                var slotInfoElement = root.querySelector('[data-bind*="slotInfo"]');
                
                if (shiftTitleElement) shiftTitleElement.textContent = self.shiftTitle();
                if (shiftDateElement) shiftDateElement.textContent = self.shiftDate();
                if (shiftTimeElement) shiftTimeElement.textContent = self.shiftTime();
                if (shiftNoteElement) shiftNoteElement.textContent = self.shiftNote();
                if (slotInfoElement) slotInfoElement.textContent = self.slotInfo();
                
                // 募集中の時刻セクションも更新
                var startTimeElement = root.querySelector('[data-bind*="shift().start_time"]');
                var endTimeElement = root.querySelector('[data-bind*="shift().end_time"]');
                
                if (startTimeElement) startTimeElement.textContent = self.shift() ? self.shift().start_time : '';
                if (endTimeElement) endTimeElement.textContent = self.shift() ? self.shift().end_time : '';
                
                console.log('Shift details updated manually');
                
                // メインコンテンツの表示を強制的に制御
                var mainContent = root.querySelector('.main-content');
                if (mainContent) {
                  if (self.isReady()) {
                    mainContent.style.display = 'flex';
                    mainContent.style.visibility = 'visible';
                    mainContent.style.opacity = '1';
                    console.log('Main content displayed manually');
                  } else {
                    mainContent.style.display = 'none';
                    mainContent.style.visibility = 'hidden';
                    mainContent.style.opacity = '0';
                  }
                }
                
                // 読み込み中メッセージの表示を制御
                var loadingDiv = root.querySelector('.loading');
                if (loadingDiv) {
                  if (self.loading()) {
                    loadingDiv.style.display = 'block';
                  } else {
                    loadingDiv.style.display = 'none';
                  }
                }
              }, 100);
            })
            .catch(function (e) {
              console.error('Load failed:', e);
              self.error('シフトの取得に失敗しました（' + (e && (e.status || e.message) || 'unknown') + '）。');
              self.shift(defaultShift);
              self.loading(false);
              self.isReady(false);
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
          console.log('Joining shift:', shiftId);
          console.log('Current shift data:', vm.shift());
          fetch('/api/shifts/' + shiftId + '/join', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              }
          })
          .then(function(response) {
              return response.json();
          })
          .then(function(data) {
              if (data.success) {
                  vm.showAlert('シフトに参加しました！', 'success');
                  vm.load(shiftId); // データを再読み込み
                  
                  // 自分のシフトページのデータも更新
                  if (typeof window.refreshMyShifts === 'function') {
                      window.refreshMyShifts();
                  }
                  
                  // 自分のシフトページにリダイレクト
                  setTimeout(function() {
                      window.location.href = '/my/shifts';
                  }, 1500);
              } else {
                  vm.showAlert('参加に失敗しました: ' + data.message, 'error');
              }
          })
          .catch(function(error) {
              console.error('Join error:', error);
              vm.showAlert('参加に失敗しました', 'error');
          });
      };
      
      // シフト参加を取消
      vm.cancelParticipation = function(shiftId) {
          console.log('Canceling participation for shift:', shiftId);
          fetch('/api/shifts/' + shiftId + '/cancel', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              }
          })
          .then(function(response) {
              return response.json();
          })
          .then(function(data) {
              if (data.success) {
                  vm.showAlert('シフト参加を取消しました', 'success');
                  vm.load(shiftId); // データを再読み込み
                  
                  // 自分のシフトページのデータも更新
                  if (typeof window.refreshMyShifts === 'function') {
                      window.refreshMyShifts();
                  }
                  
                  // 自分のシフトページにリダイレクト
                  setTimeout(function() {
                      window.location.href = '/my/shifts';
                  }, 1500);
              } else {
                  vm.showAlert('取消に失敗しました: ' + data.message, 'error');
              }
          })
          .catch(function(error) {
              console.error('Cancel error:', error);
              vm.showAlert('取消に失敗しました', 'error');
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
  
  // 3) バインディングを適用
  (function bindOnce() {
    var root = document.getElementById('shift-detail-root') || document.body;
    function bind() {
      try {
        if (window.ko) {
          ko.applyBindings(vm, root);
          console.log('Knockout binding applied ONCE');
        }
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
    console.log('Initial state - loading:', vm.loading());
    console.log('Initial state - isReady:', vm.isReady());
    console.log('Initial state - shift:', vm.shift());
    
    if (!id) {
      vm.loading(false);
      vm.error && vm.error('シフトIDがURLから取得できませんでした。');
      return;
    }
    console.log('Loading shift detail for ID:', id);
    // ここで new し直さないこと！！ vm.load を同じインスタンスに対して呼ぶ
    vm.load(id);
  })();