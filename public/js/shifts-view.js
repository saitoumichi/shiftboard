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
      vm.shiftTitle = ko.computed(function() {
          var s = vm.shift();
          console.log('shiftTitle computed - shift:', s);
          if (!s) return '';
          
          // データベースのtitleフィールドを優先的に使用
          if (s.title) {
              console.log('shiftTitle computed - using database title:', s.title);
              return s.title;
          }
          
          // フォールバック：日付と時間から生成
          var t = (s.start_time && s.end_time) ? (s.start_time.substring(0,5) + '〜' + s.end_time.substring(0,5)) : '';
          var result = (s.shift_date ? s.shift_date + ' ' : '') + t;
          console.log('shiftTitle computed - fallback result:', result);
          return result;
      });
      
      vm.shiftDate = ko.computed(function() {
          var s = vm.shift();
          console.log('shiftDate computed - shift:', s);
          var result = s ? s.shift_date : '';
          console.log('shiftDate computed - result:', result);
          return result;
      });
      
      vm.shiftTime = ko.computed(function() {
          var s = vm.shift();
          return (s && s.start_time && s.end_time) ? (s.start_time.substring(0,5) + '〜' + s.end_time.substring(0,5)) : '';
      });
      
      vm.shiftNote = ko.computed(function() {
          var s = vm.shift();
          return s ? (s.note || '備考なし') : '';
      });
      
      vm.slotInfo = ko.computed(function() {
          var s = vm.shift();
          if (!s) return '';
          var assigned = s.assigned_users ? s.assigned_users.length : 0;
          var total = s.slot_count || 0;
          return assigned + '/' + total + '人';
      });
      
      // 参加状況の判定
      vm.isParticipating = ko.computed(function() {
          var s = vm.shift();
          if (!s) {
              console.log('isParticipating: shift is null');
              return false;
          }
          
          // 現在のユーザーを特定（仮の実装）
          var currentUser = 'Alice'; // 仮のユーザー名
          var result = Array.isArray(s.assigned_users) && s.assigned_users.some(u => u.name === currentUser);
          
          console.log('isParticipating calculation:', {
              currentUser: currentUser,
              assigned_users: s.assigned_users,
              result: result
          });
          
          return result;
      });
      
      vm.canParticipate = ko.computed(function() {
          var s = vm.shift();
          if (!s) {
              console.log('canParticipate: shift is null');
              return false;
          }
          var assigned = Array.isArray(s.assigned_users) ? s.assigned_users.length : 0;
          var total = s.slot_count || 0;
          var result = assigned < total;
          console.log('canParticipate calculation:', {
              assigned: assigned,
              total: total,
              result: result,
              assigned_users: s.assigned_users
          });
          return result;
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
              
              // 手動でDOMを更新
              setTimeout(function() {
                var root = document.getElementById('shift-detail-root');
                if (root) {
                  var loadingDiv = root.querySelector('.loading');
                  var mainContent = root.querySelector('.main-content');
                  
                  if (loadingDiv) {
                    loadingDiv.style.display = 'none';
                    console.log('Loading div hidden manually');
                  }
                  if (mainContent) {
                    mainContent.style.display = 'flex';
                    mainContent.style.visibility = 'visible';
                    mainContent.style.opacity = '1';
                    console.log('Main content shown manually');
                  }
                  
                  // シフト詳細を手動で更新
                  var shiftTitleElement = root.querySelector('.shift-title');
                  var shiftDateElement = root.querySelector('.shift-date');
                  var shiftTimeElement = root.querySelector('.shift-time');
                  var shiftNoteElement = root.querySelector('.shift-note');
                  var slotInfoElement = root.querySelector('.slot-info');
                  
                  // 募集中の時刻セクションの要素
                  var startTimeElement = root.querySelector('.recruitment-details li:nth-child(1) span');
                  var endTimeElement = root.querySelector('.recruitment-details li:nth-child(2) span');
                  
                  if (shiftTitleElement) {
                    var shift = self.shift();
                    var title = 'シフト詳細';
                    if (shift) {
                      // データベースのtitleフィールドを使用
                      if (shift.title) {
                        title = shift.title;
                      } else {
                        // フォールバック：日付と時間から生成
                        var dateStr = '';
                        if (shift.shift_date) {
                          var date = new Date(shift.shift_date);
                          dateStr = (date.getMonth() + 1) + '月' + date.getDate() + '日のシフト';
                        }
                        
                        var timeStr = '';
                        if (shift.start_time && shift.end_time) {
                          timeStr = shift.start_time.substring(0,5) + '〜' + shift.end_time.substring(0,5);
                        }
                        
                        title = dateStr + (timeStr ? ' (' + timeStr + ')' : '');
                      }
                    }
                    shiftTitleElement.textContent = title;
                    console.log('Manual update - shift title:', title);
                  }
                  
                  if (shiftDateElement) {
                    var shift = self.shift();
                    var date = '';
                    if (shift && shift.shift_date) {
                      var dateObj = new Date(shift.shift_date);
                      var year = dateObj.getFullYear();
                      var month = dateObj.getMonth() + 1;
                      var day = dateObj.getDate();
                      var dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][dateObj.getDay()];
                      date = year + '年' + month + '月' + day + '日(' + dayOfWeek + ')';
                    }
                    shiftDateElement.textContent = date;
                    console.log('Manual update - shift date:', date);
                  }
                  
                  if (shiftTimeElement) {
                    var shift = self.shift();
                    var time = '';
                    if (shift && shift.start_time && shift.end_time) {
                      time = shift.start_time.substring(0,5) + '〜' + shift.end_time.substring(0,5);
                    }
                    shiftTimeElement.textContent = time;
                    console.log('Manual update - shift time:', time);
                  }
                  
                  if (shiftNoteElement) {
                    var shift = self.shift();
                    var note = shift ? (shift.note || '備考なし') : '';
                    shiftNoteElement.textContent = note;
                    console.log('Manual update - shift note:', note);
                  }
                  
                  if (slotInfoElement) {
                    var shift = self.shift();
                    var slotInfo = '';
                    if (shift) {
                      var assigned = shift.assigned_users ? shift.assigned_users.length : 0;
                      var total = shift.slot_count || 0;
                      slotInfo = assigned + '/' + total + '人';
                    }
                    slotInfoElement.textContent = slotInfo;
                    console.log('Manual update - slot info:', slotInfo);
                  }
                  
                  // 募集中の時刻を手動で更新
                  if (startTimeElement) {
                    var shift = self.shift();
                    var startTime = '';
                    if (shift && shift.start_time) {
                      startTime = shift.start_time.substring(0,5);
                    }
                    startTimeElement.textContent = startTime;
                    console.log('Manual update - start time:', startTime);
                  }
                  
                  if (endTimeElement) {
                    var shift = self.shift();
                    var endTime = '';
                    if (shift && shift.end_time) {
                      endTime = shift.end_time.substring(0,5);
                    }
                    endTimeElement.textContent = endTime;
                    console.log('Manual update - end time:', endTime);
                  }
                }
              }, 100);
              
              
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
                
                // ボタンの表示状態をデバッグ
                var participateBtn = root.querySelector('.btn-participate');
                var cancelBtn = root.querySelector('.btn-cancel');
                
                // 手動で参加状況を計算
                var shift = self.shift();
                var isParticipating = false;
                var canParticipate = false;
                
                if (shift) {
                  // 参加状況の手動計算
                  var currentUser = 'Alice';
                  isParticipating = Array.isArray(shift.assigned_users) && shift.assigned_users.some(u => u.name === currentUser);
                  
                  // 参加可能かの手動計算
                  var assigned = Array.isArray(shift.assigned_users) ? shift.assigned_users.length : 0;
                  var total = shift.slot_count || 0;
                  canParticipate = assigned < total;
                  
                  console.log('Manual calculation:', {
                    assigned: assigned,
                    total: total,
                    isParticipating: isParticipating,
                    canParticipate: canParticipate,
                    assigned_users: shift.assigned_users
                  });
                }
                
                if (participateBtn) {
                  var shouldShowParticipate = shift && !isParticipating && canParticipate;
                  console.log('Participate button should show:', shouldShowParticipate);
                  console.log('  - shift exists:', !!shift);
                  console.log('  - isParticipating:', isParticipating);
                  console.log('  - canParticipate:', canParticipate);
                  
                  if (shouldShowParticipate) {
                    participateBtn.style.display = 'block';
                    participateBtn.style.visibility = 'visible';
                  } else {
                    participateBtn.style.display = 'none';
                  }
                }
                
                if (cancelBtn) {
                  var shouldShowCancel = shift && isParticipating;
                  console.log('Cancel button should show:', shouldShowCancel);
                  console.log('  - shift exists:', !!shift);
                  console.log('  - isParticipating:', isParticipating);
                  
                  if (shouldShowCancel) {
                    cancelBtn.style.display = 'block';
                    cancelBtn.style.visibility = 'visible';
                  } else {
                    cancelBtn.style.display = 'none';
                  }
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
        console.log('joinShift called with shiftId:', shiftId);
        // モーダルダイアログを表示
        vm.showCommentModal(shiftId);
    };
      
      // コメント入力モーダルを表示
      vm.showCommentModal = function(shiftId) {
          console.log('showCommentModal called with shiftId:', shiftId);
          
          var modal = document.getElementById('comment-modal');
          var textarea = document.getElementById('comment-textarea');
          var cancelBtn = document.getElementById('comment-cancel-btn');
          var okBtn = document.getElementById('comment-ok-btn');
          
          console.log('Modal elements found:', {
              modal: !!modal,
              textarea: !!textarea,
              cancelBtn: !!cancelBtn,
              okBtn: !!okBtn
          });
          
          if (!modal) {
              console.error('comment-modal element not found!');
              console.log('Available elements with "comment" in id:', 
                  Array.from(document.querySelectorAll('[id*="comment"]')).map(el => el.id));
              console.log('All elements with id:', 
                  Array.from(document.querySelectorAll('[id]')).map(el => el.id));
              return;
          }
          
          // テキストエリアをクリア
          if (textarea) {
              textarea.value = '';
          }
          
          // モーダルを表示
          modal.style.display = 'flex';
          console.log('Modal displayed');
          textarea.focus();
          
          // キャンセルボタンのイベント
          cancelBtn.onclick = function() {
              modal.style.display = 'none';
          };
          
          // OKボタンのイベント
          okBtn.onclick = function() {
              var comment = textarea.value.trim();
              vm.submitJoinShift(shiftId, comment);
              modal.style.display = 'none';
          };
          
          // エスケープキーでモーダルを閉じる
          document.addEventListener('keydown', function(e) {
              if (e.key === 'Escape') {
                  modal.style.display = 'none';
              }
          });
          
          // モーダル外クリックで閉じる
          modal.onclick = function(e) {
              if (e.target === modal) {
                  modal.style.display = 'none';
              }
          };
      };
      
      // シフト参加を実際に実行
      vm.submitJoinShift = function(shiftId, comment) {
          console.log('Joining shift:', shiftId, 'with comment:', comment);
          
          // 現在のユーザーIDを取得（セッションから）
          var currentUserId = 1; // 仮のユーザーID（認証実装時に置き換え）
          
          fetch('/api/shift_assignments/create', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              },
              body: JSON.stringify({
                  shift_id: shiftId,
                  user_id: currentUserId,
                  status: 'assigned',
                  self_word: comment
              })
          })
          .then(function(response) {
              return response.json();
          })
          .then(function(data) {
              if (data.success) {
                  vm.showAlert(data.message || 'シフトに参加しました！', 'success');
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
                  // エラーメッセージを詳細に処理
                  var errorMessage = '参加に失敗しました';
                  if (data.message) {
                      errorMessage = data.message;
                  } else if (data.error) {
                      switch (data.error) {
                          case 'already_joined':
                              errorMessage = '既にこのシフトに参加しています';
                              break;
                          case 'shift_full':
                              errorMessage = 'このシフトの定員に達しています';
                              break;
                          case 'shift_not_found':
                              errorMessage = '指定されたシフトが見つかりません';
                              break;
                          case 'user_not_found':
                              errorMessage = '指定されたユーザーが見つかりません';
                              break;
                          case 'validation_failed':
                              errorMessage = '入力内容に誤りがあります';
                              break;
                          default:
                              errorMessage = data.error;
                      }
                  }
                  vm.showAlert(errorMessage, 'error');
              }
          })
          .catch(function(error) {
              console.error('Join error:', error);
              vm.showAlert('参加に失敗しました: ' + error.message, 'error');
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