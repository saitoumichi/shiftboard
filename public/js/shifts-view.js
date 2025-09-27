// シフト詳細ページ用JavaScript
console.log('shifts-view.js loaded');

// グローバルクリックイベントリスナー（デバッグ用）
document.addEventListener('click', function(e) {
  console.log('=== GLOBAL CLICK EVENT ===');
  console.log('Global click event:', {
    target: e.target,
    targetClass: e.target.className,
    targetTag: e.target.tagName,
    isParticipateButton: e.target.classList.contains('btn-participate'),
    path: e.composedPath ? e.composedPath().map(el => el.tagName + (el.className ? '.' + el.className : '')) : 'not supported'
  });
  
  if (e.target.classList.contains('btn-participate')) {
    console.log('=== GLOBAL CLICK DETECTED ON PARTICIPATE BUTTON ===');
  }
  
  // モーダルが表示されているかチェック
  const modal = document.getElementById('comment-modal-view');
  if (modal) {
    const modalStyle = getComputedStyle(modal);
    console.log('Modal state:', {
      display: modalStyle.display,
      visibility: modalStyle.visibility,
      opacity: modalStyle.opacity,
      zIndex: modalStyle.zIndex,
      hasShowClass: modal.classList.contains('show')
    });
  }
});

// グローバルマウスダウンイベントリスナー（デバッグ用）
document.addEventListener('mousedown', function(e) {
  console.log('=== GLOBAL MOUSEDOWN EVENT ===');
  console.log('Target:', e.target, 'Class:', e.target.className);
});

// グローバルマウスアップイベントリスナー（デバッグ用）
document.addEventListener('mouseup', function(e) {
  console.log('=== GLOBAL MOUSEUP EVENT ===');
  console.log('Target:', e.target, 'Class:', e.target.className);
});

// 未ログインガード（即リダイレクトは削除）
// 代わりに、DOM後に"操作を無効化"するだけ
document.addEventListener('DOMContentLoaded', function () {
  console.log('DOMContentLoaded event fired in shifts-view.js');
  
  // モーダルを確実に閉じる
  const modal = document.getElementById('comment-modal-view');
  if (modal) {
    console.log('Closing modal on DOMContentLoaded');
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.style.visibility = 'hidden';
    modal.style.opacity = '0';
  }
  
  const uid = Number(window.CURRENT_USER_ID || document.querySelector('meta[name="current-user-id"]')?.content || 0);
  console.log('CURRENT_USER_ID (view):', uid);
  if (!uid) {
    console.warn('未ログイン：操作を無効化');
    // 参加・取消ボタンを無効化（存在すれば）
    const btns = document.querySelectorAll('.btn-participate, .btn-cancel');
    btns.forEach(b => { b.disabled = true; b.title = 'ログインが必要です'; });
    // ここで location.href に飛ばさない
  } else {
    console.log('ログイン済み：ボタンを有効化');
    // ログイン済みの場合はボタンを有効化
    const btns = document.querySelectorAll('.btn-participate, .btn-cancel');
    btns.forEach(b => { b.disabled = false; b.title = ''; });
  }
});

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
        const v = (typeof ko.toJS === 'function') ? ko.toJS(value) : value;
        return JSON.stringify(v, replacer || null, space || 2);
      } catch (e) {
        try { return JSON.stringify(value); } catch (_) { return String(value); }
      }
    };
  }
  
  const defaultShift = {
      id: null, 
      shift_date: '', 
      start_time: '', 
      end_time: '',
      note: '', 
      recruit_count: 1, 
      available_slots: 0, 
      assigned_users: []
  };
  
  // Knockout.js ViewModel
  function ShiftDetailViewModel() {
      const vm = this;
      
      // データ
      vm.shift = ko.observable(null);
      vm.participants = ko.observableArray([]);
      vm.loading = ko.observable(true);
      vm.error = ko.observable('');
      vm.showRecruitmentDetails = ko.observable(false);
      vm.isReady = ko.observable(false);
      vm.currentUserId = ko.observable(window.CURRENT_USER_ID || 0);
      vm.confirmationShown = false; // 確認ダイアログ表示フラグ
      
      // シフト編集（モーダル表示）
      vm.editShift = function() {
          const shift = vm.shift();
          if (!shift || !shift.id) {
              console.error('No shift data available for editing');
              return;
          }
          
          // 編集モーダルを表示
          vm.showEditModal(shift);
      };
      
      // 計算プロパティ
      vm.shiftTitle = ko.computed(function() {
          const s = vm.shift();
          console.log('shiftTitle computed - shift:', s);
          if (!s) return '';
          
          // データベースのtitleフィールドを優先的に使用
          if (s.title) {
              console.log('shiftTitle computed - using database title:', s.title);
              return s.title;
          }
          
          // フォールバック：日付と時間から生成
          const t = (s.start_time && s.end_time) ? (s.start_time.substring(0,5) + '〜' + s.end_time.substring(0,5)) : '';
          const result = (s.shift_date ? s.shift_date + ' ' : '') + t;
          console.log('shiftTitle computed - fallback result:', result);
          return result;
      });
      
      vm.shiftDate = ko.computed(function() {
          const s = vm.shift();
          console.log('shiftDate computed - shift:', s);
          const result = s ? s.shift_date : '';
          console.log('shiftDate computed - result:', result);
          return result;
      });
      
      vm.shiftTime = ko.computed(function() {
          const s = vm.shift();
          return (s && s.start_time && s.end_time) ? (s.start_time.substring(0,5) + '〜' + s.end_time.substring(0,5)) : '';
      });
      
      vm.shiftNote = ko.computed(function() {
          const s = vm.shift();
          return s ? (s.note || '備考なし') : '';
      });
      
      vm.slotInfo = ko.computed(function() {
          const s = vm.shift();
          if (!s) return '';
          const assigned = s.assigned_users ? s.assigned_users.length : 0;
          const total = s.recruit_count || 0;
          return assigned + '/' + total + '人';
      });
      
      // 定員状況を手動で更新する関数
      vm.updateCapacityDisplay = function() {
          console.log('=== updateCapacityDisplay called ===');
          const shift = vm.shift();
          const participants = vm.participants();
          
          if (shift) {
              const assignedFromShift = Array.isArray(shift.assigned_users) ? shift.assigned_users.length : 0;
              const assignedFromParticipants = Array.isArray(participants) ? participants.length : 0;
              const assigned = Math.max(assignedFromShift, assignedFromParticipants);
              const total = parseInt(shift.recruit_count || shift.slot_count || 0) || 0;
              const available = total - assigned;
              
              console.log('Updating capacity display:', {
                  assigned: assigned,
                  total: total,
                  available: available,
                  isFull: available === 0 && total > 0,
                  hasSpace: available > 0
              });
              
              // 画面の要素を直接更新
              const assignedSpan = document.getElementById('capacity-assigned');
              const totalSpan = document.getElementById('capacity-total');
              const statusSpan = document.getElementById('capacity-status');
              
              if (assignedSpan) assignedSpan.textContent = assigned;
              if (totalSpan) totalSpan.textContent = total;
              if (statusSpan) {
                  if (available === 0 && total > 0) {
                      statusSpan.textContent = '(満員)';
                      statusSpan.style.color = '#d32f2f';
                      statusSpan.style.fontWeight = 'bold';
                  } else if (available > 0) {
                      statusSpan.textContent = '(空き: ' + available + '人)';
                      statusSpan.style.color = '#2e7d32';
                      statusSpan.style.fontWeight = 'bold';
                  } else {
                      statusSpan.textContent = '';
                  }
              }
              
              console.log('Capacity display updated: ' + assigned + '/' + total + '人');
              
              // シフト情報も更新
              const shiftFreeText = document.getElementById('shift-free-text');
              if (shiftFreeText && shift) {
                  shiftFreeText.textContent = shift.free_text || 'シフト情報なし';
              }
              
              // シフト作成者IDも更新
              const shiftCreatedBy = document.getElementById('shift-created-by');
              const shiftCreatedByBottom = document.getElementById('shift-created-by-bottom');
              if (shift && shift.created_by) {
                  if (shiftCreatedBy) shiftCreatedBy.textContent = shift.created_by;
                  if (shiftCreatedByBottom) shiftCreatedByBottom.textContent = shift.created_by;
              }
              
          }
      };

      // 参加状況の判定
      vm.isParticipating = ko.computed(function() {
          console.log('=== isParticipating computed called ===');
          const currentUserId = vm.currentUserId();
          console.log('Current user ID:', currentUserId, 'Type:', typeof currentUserId);
          
          if (!currentUserId) {
              console.log('isParticipating: no current user ID');
              return false;
          }
          
          // シフトデータが読み込まれていない場合は false
          const shift = vm.shift();
          if (!shift) {
              console.log('isParticipating: shift not loaded yet');
              return false;
          }
          
          const participants = vm.participants();
          console.log('isParticipating - participants:', participants);
          console.log('isParticipating - participants length:', participants.length);
          console.log('isParticipating - shift assigned_users:', shift.assigned_users);
          
          // participants配列から確認
          const resultFromParticipants = participants.some(function(p) {
              console.log('Checking participant:', p, 'user_id:', p.user_id, 'id:', p.id, 'currentUserId:', currentUserId);
              const match = p.user_id == currentUserId || p.id == currentUserId;
              console.log('Participant match:', match);
              return match;
          });
          
          // shift.assigned_usersからも確認
          let resultFromShift = false;
          if (shift.assigned_users && Array.isArray(shift.assigned_users)) {
              resultFromShift = shift.assigned_users.some(function(user) {
                  console.log('Checking assigned user:', user, 'user_id:', user.user_id, 'id:', user.id, 'currentUserId:', currentUserId);
                  const match = user.user_id == currentUserId || user.id == currentUserId;
                  console.log('Assigned user match:', match);
                  return match;
              });
          }
          
          // shift.assignmentsオブジェクトからも確認
          let resultFromAssignments = false;
          if (shift.assignments && typeof shift.assignments === 'object') {
              for (const key in shift.assignments) {
                  if (shift.assignments.hasOwnProperty(key)) {
                      const assignment = shift.assignments[key];
                      console.log('Checking assignment:', assignment, 'user_id:', assignment.user_id, 'id:', assignment.id, 'currentUserId:', currentUserId);
                      const match = assignment.user_id == currentUserId || assignment.id == currentUserId;
                      console.log('Assignment match:', match);
                      if (match) {
                          resultFromAssignments = true;
                          break;
                      }
                  }
              }
          }
          
          const result = resultFromParticipants || resultFromShift || resultFromAssignments;
          console.log('isParticipating calculation result:', result, 'fromParticipants:', resultFromParticipants, 'fromShift:', resultFromShift, 'fromAssignments:', resultFromAssignments);
          
          console.log('RETURNING RESULT:', result);
          return result;
      });
      
      // 手動でisParticipatingを再計算する関数
      vm.recalculateIsParticipating = function() {
          console.log('=== Manual isParticipating recalculation ===');
          const currentUserId = window.CURRENT_USER_ID || 0;
          console.log('Manual recalculation - Current user ID:', currentUserId);
          
          const shift = vm.shift();
          const participants = vm.participants();
          
          if (!currentUserId || !shift || !participants) {
              console.log('Manual recalculation - Missing data:', {
                  currentUserId: currentUserId,
                  shift: !!shift,
                  participants: !!participants
              });
              return false;
          }
          
          const isParticipating = participants.some(function(p) {
              return p.user_id == currentUserId || p.id == currentUserId;
          });
          
          console.log('Manual recalculation result:', isParticipating);
          return isParticipating;
      };
      
      // デバッグ情報を手動で更新する関数
      vm.updateDebugInfoManually = function() {
          console.log('=== Manual debug info update ===');
          const root = document.getElementById('shift-detail-root');
          if (!root) {
              console.error('Root element not found');
              return;
          }
          
          const debugDiv = root.querySelector('.debug-info');
          if (!debugDiv) {
              console.error('Debug div not found');
              return;
          }
          
          const spans = debugDiv.querySelectorAll('span');
          console.log('Found', spans.length, 'debug spans');
          
          // ViewModelの現在の値を取得（強制的に最新値を取得）
          console.log('=== Force getting fresh ViewModel values ===');
          const currentShift = vm.shift();
          console.log('Raw shift from vm.shift():', currentShift);
          
          const currentParticipants = vm.participants();
          console.log('Raw participants from vm.participants():', currentParticipants);
          
          const currentUserId = vm.currentUserId();
          console.log('Raw currentUserId from vm.currentUserId():', currentUserId);
          
          // シフトデータから直接参加者情報を取得
          let directParticipants = [];
          if (currentShift && currentShift.assigned_users) {
              directParticipants = currentShift.assigned_users;
              console.log('Direct participants from shift.assigned_users:', directParticipants);
          }
          
          // 実際の参加者数を使用
          const actualParticipantCount = directParticipants.length;
          console.log('Actual participant count:', actualParticipantCount);
          
          // 参加状況を手動で計算
          let manualIsParticipating = false;
          if (currentUserId && directParticipants.length > 0) {
              manualIsParticipating = directParticipants.some(function(p) {
                  return p.user_id == currentUserId || p.id == currentUserId;
              });
          }
          console.log('Manual isParticipating calculation:', manualIsParticipating);
          
          // 参加可能かを手動で計算
          let manualCanParticipate = false;
          if (currentShift) {
              const total = parseInt(currentShift.recruit_count) || 0;
              const assigned = directParticipants.length;
              manualCanParticipate = assigned < total;
          }
          console.log('Manual canParticipate calculation:', manualCanParticipate);
          
          console.log('ViewModel values:', {
              shift: currentShift,
              participants: currentParticipants,
              currentUserId: currentUserId,
              isParticipating: manualIsParticipating,
              canParticipate: manualCanParticipate
          });
          
          spans.forEach(function(span, index) {
              const dataBind = span.getAttribute('data-bind');
              console.log('Span', index, 'data-bind:', dataBind);
              
              if (dataBind) {
                  try {
                      // data-bindの内容に応じて値を設定（手動計算値を使用）
                      if (dataBind.includes('シフト存在')) {
                          console.log('Checking shift existence - currentShift:', currentShift, 'type:', typeof currentShift);
                          span.textContent = currentShift ? 'あり' : 'なし';
                      } else if (dataBind.includes('シフトID')) {
                          span.textContent = currentShift && currentShift.id ? currentShift.id : 'なし';
                      } else if (dataBind.includes('参加状況')) {
                          span.textContent = manualIsParticipating ? '参加中' : '未参加';
                      } else if (dataBind.includes('参加可能')) {
                          span.textContent = manualCanParticipate ? '可能' : '不可能';
                      } else if (dataBind.includes('現在のユーザーID')) {
                          span.textContent = currentUserId || 'なし';
                      } else if (dataBind.includes('参加者数')) {
                          span.textContent = actualParticipantCount;
                      } else if (dataBind.includes('定員状況')) {
                          let slotInfo = '';
                          if (currentShift) {
                              const assigned = directParticipants.length;
                              const total = parseInt(currentShift.recruit_count) || 0;
                              slotInfo = assigned + '/' + total;
                          }
                          span.textContent = slotInfo || 'なし';
                      }
                      console.log('Updated span', index, 'to:', span.textContent);
                  } catch (e) {
                      console.error('Error updating span', index, ':', e);
                  }
              }
          });
          
          // 参加者一覧も手動で更新
          const participantsListDiv = root.querySelector('.participants-list');
          if (participantsListDiv) {
              console.log('Updating participants list manually...');
              
              // shift.assigned_usersから直接参加者情報を取得
              let actualParticipants = [];
              if (currentShift && currentShift.assigned_users && currentShift.assigned_users.length > 0) {
                  actualParticipants = currentShift.assigned_users;
                  console.log('Using assigned_users:', actualParticipants);
              } else if (currentParticipants && currentParticipants.length > 0) {
                  actualParticipants = currentParticipants;
                  console.log('Using participants array:', actualParticipants);
              }
              
              console.log('Final participants to display:', actualParticipants);
              
              if (actualParticipants.length > 0) {
                  let participantsHTML = '';
                  
                  // シンプルなHTMLエスケープ（改行のみ除去）
                  function simpleEscape(text) {
                      if (!text) return '';
                      return text.toString().replace(/\n/g, '').replace(/\r/g, '');
                  }
                  
                  actualParticipants.forEach(function(participant) {
                      console.log('Adding participant:', participant);
                      
                      const participantName = simpleEscape(participant.name);
                      const participantComment = simpleEscape(participant.self_word);
                      const participantColor = participant.color || '#cccccc';
                      
                      participantsHTML += 
                          '<div class="participant-item" style="display: flex; align-items: center; margin: 5px 0; padding: 8px; border-radius: 4px; background: #f9f9f9;">' +
                              '<div class="participant-color" style="width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; flex-shrink: 0; background-color: ' + participantColor + '"></div>' +
                              '<div style="flex: 1; display: flex; align-items: center; gap: 8px;">' +
                                  '<div class="participant-name" style="font-weight: bold; color: #333;">' + participantName + '</div>';
                      
                      if (participantComment && participantComment.trim() !== '') {
                          participantsHTML += '<div class="participant-comment" style="font-style: italic; color: #666; font-size: 0.9em;">' + participantComment + '</div>';
                      }
                      
                      participantsHTML += 
                              '</div>' +
                          '</div>';
                  });
                  participantsListDiv.innerHTML = participantsHTML;
                  console.log('Participants list updated with ' + actualParticipants.length + ' participants');
              } else {
                  participantsListDiv.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">参加者はいません</div>';
                  console.log('No participants found, showing empty message');
              }
          } else {
              console.error('Participants list div not found');
          }
          
          console.log('Debug info and participants list manually updated successfully');
      };
      
      vm.canParticipate = ko.computed(function() {
          const s = vm.shift();
          if (!s) {
              console.log('canParticipate: shift is null');
              return false;
          }
          const assigned = Array.isArray(s.assigned_users) ? s.assigned_users.length : 0;
          const total = parseInt(s.recruit_count || s.slot_count || 0) || 0;
          const result = assigned < total;
          console.log('canParticipate calculation:', {
              assigned: assigned,
              total: total,
              result: result,
              assigned_users: s.assigned_users,
              recruit_count: s.recruit_count
          });
          return result;
      });
      
      // 定員状況のcomputed observable
      vm.capacityInfo = ko.computed(function() {
          const s = vm.shift();
          const p = vm.participants();
          console.log('capacityInfo called - shift:', s, 'participants:', p);
          
          if (!s) {
              console.log('capacityInfo: shift is null');
              return {
                  assigned: 0,
                  total: 0,
                  available: 0,
                  isFull: false,
                  hasSpace: false
              };
          }
          
          // assigned_users と participants の両方を考慮
          const assignedFromShift = Array.isArray(s.assigned_users) ? s.assigned_users.length : 0;
          const assignedFromParticipants = Array.isArray(p) ? p.length : 0;
          const assigned = Math.max(assignedFromShift, assignedFromParticipants);
          
          const total = parseInt(s.recruit_count || s.slot_count || 0) || 0;
          const available = total - assigned;
          
          const result = {
              assigned: assigned,
              total: total,
              available: available,
              isFull: available === 0 && total > 0,
              hasSpace: available > 0
          };
          
          console.log('capacityInfo calculation:', {
              shift: s,
              participants: p,
              assignedFromShift: assignedFromShift,
              assignedFromParticipants: assignedFromParticipants,
              assigned: assigned,
              recruit_count: s.recruit_count,
              slot_count: s.slot_count,
              parsed_total: total,
              result: result
          });
          return result;
      });
      
      // デバッグ用 JSON（テンプレから <pre data-bind="text: debugJSON"> で参照）
      vm.debugJSON = ko.pureComputed(function () {
          const s = vm.shift();
          try {
              return ko.toJSON(s, null, 2);
          } catch (e) {
              try { return JSON.stringify(s, null, 2); } catch (_) { return String(s); }
          }
      });
      
      // 現在のシフトを再読込
      vm.loadShiftDetail = function () {
          const s = vm.shift && vm.shift();
          if (s && s.id) {
              vm.load(s.id);
          }
      };
      
      // アラート表示
      vm.showAlert = function(message, type) {
          const alert = document.createElement('div');
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
          const self = this;
          console.log('Load function called with ID:', id);
          self.loading(true);
          self.error('');
          self.isReady(false);
          
          const firstTriedUrl = apiUrlFor(id);
          const fallbackTriedUrl = apiFallbackUrlFor(id);
          console.log('First URL:', firstTriedUrl);
          console.log('Fallback URL:', fallbackTriedUrl);
  
          function doFetch(url) {
            return fetch(url, { headers: { 'Accept': 'application/json' } })
              .then(function (r) {
                if (!r.ok) {
                  const err = new Error('HTTP ' + r.status);
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
              const payload = json && (json.data || json);
              if (!payload || !payload.id) throw new Error('Invalid payload');
              console.log('Setting shift data:', payload);
              console.log('Assigned users in payload:', payload.assigned_users);
              
              // 参加者情報をクリア
              self.participants.removeAll();
              
              // 参加者情報を追加
              if (payload.assigned_users && Array.isArray(payload.assigned_users)) {
                console.log('Adding participants:', payload.assigned_users);
                const participantsToAdd = [];
                payload.assigned_users.forEach(function (user) { 
                  console.log('Adding participant:', user);
                  // 参加者情報を正しい形式で追加
                  const participant = {
                    id: user.id || user.user_id,
                    user_id: user.user_id || user.id,
                    name: user.name || 'Unknown User',
                    status: user.status || 'assigned',
                    self_word: user.self_word || '',
                    color: user.color || '#000000'
                  };
                  participantsToAdd.push(participant); 
                });
                
                // 既存の参加者をクリアして新しい参加者を追加
                self.participants.removeAll();
                self.participants(participantsToAdd);
                
                console.log('Participants after adding:', self.participants());
                console.log('First participant details:', self.participants()[0]);
                
                
              } else {
                console.log('No assigned_users found or not an array');
                console.log('Payload structure:', Object.keys(payload));
              }
  
              self.shift(payload);
              self.loading(false);
              self.isReady(true);
              
                     // Knockout.jsのバインディングを強制的に更新
                     if (typeof ko !== 'undefined' && ko.processAllDeferredBindingUpdates) {
                       ko.processAllDeferredBindingUpdates();
                     }
                     
                     // バインディングが適用されていない場合は再適用
                     if (!bindingApplied) {
                       try {
                         const root = document.getElementById('shift-detail-root') || document.body;
                         ko.applyBindings(vm, root);
                         bindingApplied = true;
                         console.log('Knockout binding applied after data load');
                         
                         // バインディング適用後にデバッグ情報を強制更新
                         setTimeout(function() {
                           console.log('=== バインディング適用後のデバッグ情報確認 ===');
                           const debugDiv = root.querySelector('.debug-info');
                           if (debugDiv) {
                             const spans = debugDiv.querySelectorAll('span');
                             console.log('Debug spans after binding:', spans.length);
                             spans.forEach(function(span, index) {
                               console.log('Span ' + index + ':', span.textContent);
                             });
                           }
                         }, 50);
                         
                       } catch (e) {
                         console.error('Binding error after data load:', e);
                       }
                     } else {
                       // バインディングが既に適用されている場合は、observableの更新を強制
                       console.log('=== 強制的にobservableを更新 ===');
                       try {
                         // 現在の値を取得して再設定することで更新を強制
                         const currentShift = vm.shift();
                         const currentParticipants = vm.participants();
                         const currentUserId = vm.currentUserId();
                         
                         // 直接値を再設定（nullクリアを削除）
                         vm.shift(currentShift);
                         vm.participants(currentParticipants);
                         vm.currentUserId(currentUserId);
                         console.log('Observables force updated');
                       } catch (e) {
                         console.error('Error force updating observables:', e);
                       }
                     }
                     
              
              console.log('Data loaded successfully');
              console.log('isReady after load:', self.isReady());
              console.log('shift after load:', self.shift());
              console.log('Final participants count:', self.participants().length);
              console.log('Final participants data:', self.participants());
              
              // 定員状況を自動更新
              self.updateCapacityDisplay();
              
              // 最小限のデバッグ情報
              const shift = self.shift();
              if (shift) {
                console.log('Shift data:', {
                  id: shift.id,
                  recruit_count: shift.recruit_count,
                  slot_count: shift.slot_count,
                  assigned_users: shift.assigned_users ? shift.assigned_users.length : 0
                });
                console.log('Capacity info:', self.capacityInfo());
              } else {
                console.error('Shift is null after data load!');
              }
              
              
              // シフト情報と参加者リストを強制的に表示
              setTimeout(function() {
                const shift = self.shift();
                if (shift) {
                  // シフト情報を更新
                  const freeTextEl = document.getElementById('shift-free-text');
                  const idTextEl = document.getElementById('shift-id-text');
                  if (freeTextEl) {
                    freeTextEl.textContent = shift.free_text || 'シフト情報なし';
                    console.log('Auto-updated free text:', shift.free_text);
                  }
                  if (idTextEl) {
                    const span = idTextEl.querySelector('span');
                    if (span) {
                      span.textContent = shift.id;
                      console.log('Auto-updated shift ID:', shift.id);
                    }
                  }
                  
                  // 参加者リストを手動で更新
                  if (shift.assigned_users && shift.assigned_users.length > 0) {
                    console.log('Manually updating participants list from assigned_users:', shift.assigned_users);
                    
                    // participants配列を手動で更新
                    self.participants.removeAll();
                    shift.assigned_users.forEach(function(participant) {
                      self.participants.push(participant);
                      console.log('Added participant:', participant);
                    });
                    
                    console.log('Final participants count after manual update:', self.participants().length);
                  }
                }
              }, 100);
              
              // isParticipating のcomputedが正しく動作するかテスト
              setTimeout(function() {
                console.log('Testing isParticipating computed after data load');
                console.log('Current participants:', self.participants());
                console.log('Current isParticipating value:', self.isParticipating());
              }, 100);
              
              // 参加者一覧を手動で更新
              setTimeout(function() {
                const participantsList = document.querySelector('.participants-list');
                if (participantsList) {
                  console.log('Manually updating participants list');
                  const participants = self.participants();
                  console.log('Participants for manual update:', participants);
                  
                  // shift.assigned_usersから参加者を取得して手動で更新
                  const shift = self.shift();
                  if (shift && shift.assigned_users && shift.assigned_users.length > 0) {
                    console.log('Found assigned_users in shift, updating participants manually');
                    self.participants.removeAll();
                    shift.assigned_users.forEach(function(participant) {
                      self.participants.push(participant);
                      console.log('Manually added participant:', participant);
                    });
                    participants = self.participants(); // 更新された参加者を取得
                    console.log('Participants after manual update:', participants);
                  }
                  
                  // 既存の参加者アイテムと「参加者なし」表示をクリア（デバッグ情報以外）
                  const existingItems = participantsList.querySelectorAll('.participant-item, .no-participants');
                  existingItems.forEach(function(item) {
                    item.remove();
                  });
                  
                  // 参加者を手動で追加
                  if (participants && participants.length > 0) {
                    participants.forEach(function(participant) {
                      console.log('Manually adding participant:', participant);
                      const item = document.createElement('div');
                      item.className = 'participant-item';
                      item.style.cssText = 'display: flex; align-items: center; margin: 5px 0; padding: 8px; border-radius: 4px; background: #f9f9f9;';
                      
                      const colorDiv = document.createElement('div');
                      colorDiv.className = 'participant-color';
                      colorDiv.style.cssText = 'width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; flex-shrink: 0;';
                      colorDiv.style.backgroundColor = participant.color || '#000000';
                      
                      const contentDiv = document.createElement('div');
                      contentDiv.style.cssText = 'flex: 1;';
                      
                      const nameDiv = document.createElement('div');
                      nameDiv.className = 'participant-name';
                      nameDiv.style.cssText = 'font-weight: bold; color: #333;';
                      nameDiv.textContent = participant.name || 'Unknown';
                      
                      contentDiv.appendChild(nameDiv);
                      
                      if (participant.self_word && participant.self_word.trim() !== '') {
                        const commentDiv = document.createElement('div');
                        commentDiv.className = 'participant-comment';
                        commentDiv.style.cssText = 'font-style: italic; color: #666; font-size: 0.9em; margin-top: 2px;';
                        commentDiv.textContent = participant.self_word;
                        contentDiv.appendChild(commentDiv);
                      }
                      
                      // 参加状況を追加
                      const statusDiv = document.createElement('div');
                      statusDiv.className = 'participant-status';
                      statusDiv.style.cssText = 'margin-left: 8px; padding: 2px 6px; border-radius: 12px; font-size: 0.8em; font-weight: bold;';
                      
                      const status = participant.status || 'assigned';
                      const statusText = status === 'assigned' ? '参加' : (status === 'cancelled' ? '欠席' : status);
                      statusDiv.textContent = statusText;
                      
                      // ステータスに応じて色を設定
                      if (status === 'assigned') {
                        statusDiv.style.backgroundColor = '#e8f5e8';
                        statusDiv.style.color = '#2d5a2d';
                        statusDiv.classList.add('status-assigned');
                      } else if (status === 'cancelled') {
                        statusDiv.style.backgroundColor = '#ffe8e8';
                        statusDiv.style.color = '#8b0000';
                        statusDiv.classList.add('status-cancelled');
                      } else {
                        statusDiv.style.backgroundColor = '#f0f0f0';
                        statusDiv.style.color = '#666';
                        statusDiv.classList.add('status-other');
                      }
                      
                      item.appendChild(colorDiv);
                      item.appendChild(contentDiv);
                      item.appendChild(statusDiv);
                      participantsList.appendChild(item);
                    });
                  } else {
                    const noParticipants = document.createElement('div');
                    noParticipants.className = 'no-participants';
                    noParticipants.style.cssText = 'color: #999;';
                    noParticipants.textContent = '参加者なし';
                    participantsList.appendChild(noParticipants);
                  }
                }
              }, 100);
              
              // データ読み込み後にバインディングを再適用（必要に応じて）
              setTimeout(function() {
                try {
                  if (window.ko && !bindingApplied) {
                    const root = document.getElementById('shift-detail-root') || document.body;
                    ko.applyBindings(vm, root);
                    bindingApplied = true;
                    console.log('Knockout binding applied after data load');
                  }
                  
                  // モーダルを強制的に閉じる
                  const modal = document.getElementById('comment-modal-view');
                  if (modal) {
                    console.log('Closing modal after data load');
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    modal.style.visibility = 'hidden';
                    modal.style.opacity = '0';
                  }
                  
                  // ボタンの状態を強制的に更新
                  const participateBtn = document.querySelector('.btn-participate');
                  if (participateBtn) {
                    console.log('Force updating button state after data load');
                    participateBtn.style.display = 'block';
                    participateBtn.style.visibility = 'visible';
                    participateBtn.style.opacity = '1';
                    participateBtn.style.pointerEvents = 'auto';
                    participateBtn.style.position = 'relative';
                    participateBtn.style.zIndex = '1000';
                    console.log('Button state updated:', {
                      display: participateBtn.style.display,
                      visibility: participateBtn.style.visibility,
                      opacity: participateBtn.style.opacity,
                      pointerEvents: participateBtn.style.pointerEvents
                    });
                    
                    // データ読み込み後にもフォールバックリスナーを追加（重複チェック付き）
                    if (!participateBtn.hasAttribute('data-bind-processed')) {
                      console.log('Adding additional fallback listener after data load');
                      participateBtn.addEventListener('click', function(e) {
                        console.log('=== Additional fallback click handler triggered ===');
                        const vm = window.__shiftVM;
                        if (vm && vm.joinShift) {
                          console.log('Calling vm.joinShift() from additional fallback handler');
                          try {
                            vm.joinShift();
                          } catch (error) {
                            console.error('Error in additional fallback handler:', error);
                          }
                        }
                      });
                      participateBtn.setAttribute('data-bind-processed', 'true');
                    }
                  }
                } catch (e) {
                  console.error('Rebinding error:', e);
                }
              }, 50);
              
              // 手動でDOMを更新
              setTimeout(function() {
                const root = document.getElementById('shift-detail-root');
                if (root) {
                  const loadingDiv = root.querySelector('.loading');
                  const mainContent = root.querySelector('.main-content');
                  
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
                  
                  // Knockout.jsのバインディングも更新
                  self.isReady(true);
                  
                  // シフト詳細を手動で更新
                  const shiftTitleElement = root.querySelector('.shift-title');
                  const shiftDateElement = root.querySelector('.shift-date');
                  const shiftTimeElement = root.querySelector('.shift-time');
                  const shiftNoteElement = root.querySelector('.shift-note');
                  const slotInfoElement = root.querySelector('.slot-info');
                  
                  // 募集中の時刻セクションの要素
                  const startTimeElement = root.querySelector('.recruitment-details li:nth-child(1) span');
                  const endTimeElement = root.querySelector('.recruitment-details li:nth-child(2) span');
                  
                  if (shiftTitleElement) {
                    const shift = self.shift();
                    let title = 'シフト詳細';
                    if (shift) {
                      // データベースのtitleフィールドを使用
                      if (shift.title) {
                        title = shift.title;
                      } else {
                        // フォールバック：日付と時間から生成
                        let dateStr = '';
                        if (shift.shift_date) {
                          const date = new Date(shift.shift_date);
                          dateStr = (date.getMonth() + 1) + '月' + date.getDate() + '日のシフト';
                        }
                        
                        let timeStr = '';
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
                    const shift = self.shift();
                    let date = '';
                    if (shift && shift.shift_date) {
                      const dateObj = new Date(shift.shift_date);
                      const year = dateObj.getFullYear();
                      const month = dateObj.getMonth() + 1;
                      const day = dateObj.getDate();
                      const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][dateObj.getDay()];
                      date = year + '年' + month + '月' + day + '日(' + dayOfWeek + ')';
                    }
                    shiftDateElement.textContent = date;
                    console.log('Manual update - shift date:', date);
                  }
                  
                  if (shiftTimeElement) {
                    const shift = self.shift();
                    let time = '';
                    if (shift && shift.start_time && shift.end_time) {
                      time = shift.start_time.substring(0,5) + '〜' + shift.end_time.substring(0,5);
                    }
                    shiftTimeElement.textContent = time;
                    console.log('Manual update - shift time:', time);
                  }
                  
                  if (shiftNoteElement) {
                    const shift = self.shift();
                    const note = shift ? (shift.note || '備考なし') : '';
                    shiftNoteElement.textContent = note;
                    console.log('Manual update - shift note:', note);
                  }
                  
                  if (slotInfoElement) {
                    const shift = self.shift();
                    let slotInfo = '';
                    if (shift) {
                      const assigned = shift.assigned_users ? shift.assigned_users.length : 0;
                      const total = shift.recruit_count || 0;
                      slotInfo = assigned + '/' + total + '人';
                    }
                    slotInfoElement.textContent = slotInfo;
                    console.log('Manual update - slot info:', slotInfo);
                  }
                  
                  // 募集中の時刻を手動で更新
                  if (startTimeElement) {
                    const shift = self.shift();
                    let startTime = '';
                    if (shift && shift.start_time) {
                      startTime = shift.start_time.substring(0,5);
                    }
                    startTimeElement.textContent = startTime;
                    console.log('Manual update - start time:', startTime);
                  }
                  
                  if (endTimeElement) {
                    const shift = self.shift();
                    let endTime = '';
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
                     
                     const root = document.getElementById('shift-detail-root') || document.body;
                     
                     // デバッグ情報を即座に手動更新
                     const debugDiv = root.querySelector('.debug-info');
                     if (debugDiv) {
                       const shiftExistsSpan = debugDiv.querySelector('span[data-bind*="shift存在"]');
                       const shiftIdSpan = debugDiv.querySelector('span[data-bind*="シフトID"]');
                       const isParticipatingSpan = debugDiv.querySelector('span[data-bind*="参加状況"]');
                       const canParticipateSpan = debugDiv.querySelector('span[data-bind*="参加可能"]');
                       const slotInfoSpan = debugDiv.querySelector('span[data-bind*="定員状況"]');
                       const currentUserIdSpan = debugDiv.querySelector('span[data-bind*="現在のユーザーID"]');
                       const participantsCountSpan = debugDiv.querySelector('span[data-bind*="参加者数"]');
                       
                       if (shiftExistsSpan) shiftExistsSpan.textContent = self.shift() ? 'あり' : 'なし';
                       if (shiftIdSpan) shiftIdSpan.textContent = self.shift() && self.shift().id || 'なし';
                       if (isParticipatingSpan) isParticipatingSpan.textContent = self.isParticipating() ? '参加中' : '未参加';
                       if (canParticipateSpan) canParticipateSpan.textContent = self.canParticipate() ? '可能' : '不可能';
                       if (slotInfoSpan) {
                         const shift = self.shift();
                         const slotInfo = shift ? (shift.assigned_users ? shift.assigned_users.length : 0) + '/' + (shift.recruit_count || 0) : 'なし';
                         slotInfoSpan.textContent = slotInfo;
                       }
                       if (currentUserIdSpan) currentUserIdSpan.textContent = window.CURRENT_USER_ID || 'なし';
                       if (participantsCountSpan) participantsCountSpan.textContent = self.participants().length;
                       
                       console.log('Debug info updated immediately after data load');
                     }
                
                // ボタンの表示状態をデバッグ
                const participateBtn = root.querySelector('.btn-participate');
                const cancelBtn = root.querySelector('.btn-cancel');
                
                console.log('Button elements found:', {
                  participateBtn: !!participateBtn,
                  cancelBtn: !!cancelBtn
                });
                
                // 手動で参加状況を計算
                const shift = self.shift();
                let isParticipating = false;
                let canParticipate = false;
                
                if (shift) {
                  // 参加状況の手動計算（現在のユーザーIDを使用）
                  const currentUserId = window.CURRENT_USER_ID || 0;
                  console.log('Manual calculation - currentUserId:', currentUserId);
                  
                  if (Array.isArray(shift.assigned_users)) {
                    isParticipating = shift.assigned_users.some(function(user) {
                      const match = user.user_id == currentUserId || user.id == currentUserId;
                      console.log('Manual check - user:', user, 'match:', match);
                      return match;
                    });
                  }
                  
                  // 参加可能かの手動計算
                  const assigned = Array.isArray(shift.assigned_users) ? shift.assigned_users.length : 0;
                  const total = shift.recruit_count || 0;
                  canParticipate = assigned < total;
                  
                  console.log('Manual calculation:', {
                    currentUserId: currentUserId,
                    assigned: assigned,
                    total: total,
                    isParticipating: isParticipating,
                    canParticipate: canParticipate,
                    assigned_users: shift.assigned_users
                  });
                }
                
                if (participateBtn) {
                  const shouldShowParticipate = shift && !isParticipating && canParticipate;
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
                  const shouldShowCancel = shift && isParticipating;
                  console.log('Cancel button should show:', shouldShowCancel);
                  console.log('  - shift exists:', !!shift);
                  console.log('  - isParticipating:', isParticipating);
                  console.log('Cancel button current style:', {
                    display: cancelBtn.style.display,
                    visibility: cancelBtn.style.visibility
                  });
                  
                  // テスト用：取消ボタンを強制的に表示（参加ボタンが非表示の場合）
                  const shouldShowCancelForTest = !shouldShowParticipate || true; // テスト用に常に表示
                  
                  if (shouldShowCancelForTest) {
                    cancelBtn.style.display = 'block';
                    cancelBtn.style.visibility = 'visible';
                    console.log('Cancel button shown manually (test mode)');
                  } else {
                    cancelBtn.style.display = 'none';
                    console.log('Cancel button hidden manually');
                  }
                } else {
                  console.log('Cancel button not found in DOM');
                }
                
                // アクションボタンエリアのデバッグ情報も手動更新
                const actionDebugDiv = root.querySelector('[style*="background: #f0f0f0"]');
                if (actionDebugDiv) {
                  const isParticipatingSpan = actionDebugDiv.querySelector('span[data-bind*="isParticipating"]');
                  const canParticipateSpan = actionDebugDiv.querySelector('span[data-bind*="canParticipate"]');
                  const shiftExistsSpan = actionDebugDiv.querySelector('span[data-bind*="shift存在"]');
                  const participantsCountSpan = actionDebugDiv.querySelector('span[data-bind*="参加者数"]');
                  const slotCountSpan = actionDebugDiv.querySelector('span[data-bind*="定員"]');
                  
                  // 手動で値を計算
                  const shift = self.shift();
                  let isParticipating = false;
                  let canParticipate = false;
                  
                  if (shift) {
                      // 参加状況の判定（デバッグ用に1を固定）
                      const currentUserId = 1;
                      isParticipating = shift.assigned_users && shift.assigned_users.some(function(user) {
                          return user.id === currentUserId || user.name === 'Alice';
                      });
                      
                      // 参加可能かの判定
                      const assigned = shift.assigned_users ? shift.assigned_users.length : 0;
                      canParticipate = assigned < shift.recruit_count;
                  }
                  
                  if (isParticipatingSpan) isParticipatingSpan.textContent = isParticipating ? 'true' : 'false';
                  if (canParticipateSpan) canParticipateSpan.textContent = canParticipate ? 'true' : 'false';
                  if (shiftExistsSpan) shiftExistsSpan.textContent = shift ? 'あり' : 'なし';
                  if (participantsCountSpan) participantsCountSpan.textContent = shift ? shift.assigned_users.length : 0;
                  if (slotCountSpan) slotCountSpan.textContent = shift ? shift.recruit_count : 0;
                  
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
          const shift = vm.shift();
          if (!shift) return;
          
          // 既に参加しているかチェック
          const isParticipating = vm.participants().some(function(p) {
              return p.user_id === 1; // 仮のユーザーID
          });
          
          if (isParticipating) {
              vm.cancelShift(shift);
          } else {
              vm.joinShift(shift);
          }
      };
      
    // シフト参加
    vm.joinShift = function(data, event) {
        console.log('=== joinShift called ===');
        console.log('joinShift called with data:', data);
        console.log('joinShift called with event:', event);
        console.log('Current shift data:', vm.shift());
        console.log('Can participate:', vm.canParticipate());
        
        // 現在のシフトを取得
        const shift = vm.shift();
        console.log('Using current shift:', shift);
        
        if (!shift) {
            console.error('No shift data available');
            vm.showAlert('シフト情報がありません', 'error');
            return;
        }
        
        // モーダルダイアログを表示
        vm.showCommentModal(shift);
    };
    
    // シフト取消
    vm.cancelShift = function(data, event) {
        console.log('=== cancelShift called ===');
        console.log('cancelShift called with data:', data);
        console.log('cancelShift called with event:', event);
        console.log('Arguments length:', arguments.length);
        console.log('All arguments:', Array.from(arguments));
        
        // 現在のシフトを取得
        const shift = vm.shift();
        console.log('Using current shift:', shift);
        console.log('Shift ID:', shift ? shift.id : 'no shift');
        console.log('Current user ID:', vm.currentUserId());
        console.log('Is participating:', vm.isParticipating());
        
        if (!shift || !shift.id) {
            console.error('No shift data or shift ID available');
            vm.showAlert('シフト情報がありません', 'error');
            return;
        }
        
        console.log('Canceling shift ID:', shift.id);
        
        // 確認ダイアログは1度だけ表示
        if (!vm.confirmationShown) {
            if (!confirm('このシフトの参加を取り消しますか？')) {
                return;
            }
            vm.confirmationShown = true;
        } else {
            console.log('Confirmation already shown, proceeding with cancellation...');
        }
        
        const API = window.API_BASE || '/api';
        
        $.ajax({
            url: `${API}/shifts/${shift.id}/cancel`,
            type: 'POST',
            data: {
                csrf_token: 'dummy_token' // 簡易実装
            },
            success: function(response, status, xhr) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        vm.showAlert('シフトの参加を取り消しました', 'success');
                        
                        // データを再読み込み（確実に実行）
                        console.log('=== 取消後のデータ再読み込み開始 ===');
                        vm.loadShiftDetail();
                        
                        // データ再読み込み完了後にUIを更新
                        setTimeout(function() {
                            console.log('=== 取消後のUI更新開始 ===');
                            
                            // 現在の状態を確認
                            const currentShift = vm.shift();
                            const currentParticipants = vm.participants();
                            console.log('取消後の状態:', {
                                shift: currentShift,
                                participants: currentParticipants,
                                isParticipating: vm.isParticipating(),
                                canParticipate: vm.canParticipate()
                            });
                            
                            // 参加ボタンを表示、取消ボタンを非表示
                            const participateBtn = document.querySelector('.btn-participate');
                            const cancelBtn = document.querySelector('.btn-cancel');
                            
                            if (participateBtn) {
                                participateBtn.style.display = 'block';
                                participateBtn.style.visibility = 'visible';
                                participateBtn.style.opacity = '1';
                                console.log('参加ボタンを表示しました');
                            }
                            
                            if (cancelBtn) {
                                cancelBtn.style.display = 'none';
                                console.log('取消ボタンを非表示にしました');
                            }
                            
                            // 強制的にデバッグ情報を更新
                            console.log('デバッグ情報を更新中...');
                        }, 1000);
                        
                        // シフト一覧ページのデータも更新
                        if (typeof window.refreshShiftList === 'function') {
                            window.refreshShiftList();
                        }
                        
                        // 自分のシフトページのデータも更新
                        if (typeof window.refreshMyShifts === 'function') {
                            window.refreshMyShifts();
                        }
                    } else {
                        vm.showAlert('シフトの取消に失敗しました: ' + data.message, 'error');
                    }
                } catch (e) {
                    vm.showAlert('シフトの取消に失敗しました', 'error');
                    console.error('JSON Parse Error:', e);
                }
            },
            error: function(xhr, status, error) {
                const errorMessage = 'シフトの取消に失敗しました';
                
                if (xhr.status === 404) {
                    errorMessage = 'このシフトに参加していません';
                } else if (xhr.status === 405) {
                    errorMessage = 'APIエンドポイントがサポートされていません。管理者にお問い合わせください。';
                } else if (xhr.status === 409) {
                    errorMessage = 'シフトの取消ができません';
                }
                
                alert(errorMessage);
                console.error('AJAX Error:', error, xhr.responseText);
            }
        });
    };
      
      // コメント入力モーダルを表示
      vm.showCommentModal = function(shift) {
          console.log('=== showCommentModal 開始 ===');
          console.log('showCommentModal called with shift:', shift);
          
          const modal = document.getElementById('comment-modal-view');
          console.log('モーダル要素取得結果:', modal);
          const textarea = document.getElementById('comment-textarea-view');
          const cancelBtn = document.getElementById('comment-cancel-btn-view');
          const okBtn = document.getElementById('comment-ok-btn-view');
          
          console.log('Modal elements found:', {
              modal: !!modal,
              textarea: !!textarea,
              cancelBtn: !!cancelBtn,
              okBtn: !!okBtn
          });
          
          if (!modal) {
              console.error('comment-modal-view element not found!');
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
          
          // モーダルを表示（クラス管理のみ）
          console.log('モーダル表示前のクラス:', modal.className);
          modal.classList.add('show');
          console.log('モーダル表示後のクラス:', modal.className);
          
          // モーダルの表示を強制的に設定（シフト一覧と統一）
          modal.style.cssText = 'position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background: rgba(0,0,0,0.8) !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important;';
          
          console.log('Modal displayed with forced styles');
          
          // ダイアログボックスのスタイルはHTMLで既に設定済み
          console.log('Modal dialog styles already applied in HTML');
          
          // デバッグ用即席チェック
          console.log('=== デバッグチェック（詳細ページ） ===');
          console.log('1. モーダル要素存在チェック:', document.getElementById('comment-modal-view') ? 'OK' : 'NG');
          console.log('2. 計算されたdisplay:', getComputedStyle(document.getElementById('comment-modal-view')).display);
          console.log('3. z-index:', getComputedStyle(document.getElementById('comment-modal-view')).zIndex);
          console.log('4. position:', getComputedStyle(document.getElementById('comment-modal-view')).position);
          console.log('5. visibility:', getComputedStyle(document.getElementById('comment-modal-view')).visibility);
          console.log('6. opacity:', getComputedStyle(document.getElementById('comment-modal-view')).opacity);
          console.log('7. transform:', getComputedStyle(document.getElementById('comment-modal-view')).transform);
          console.log('=====================================');
          
          textarea.focus();
          
          // キャンセルボタンのイベント
          cancelBtn.onclick = function() {
              modal.classList.remove('show');
          };
          
          // OKボタンのイベント
          okBtn.onclick = function() {
              const comment = textarea.value.trim();
              vm.submitJoinShift(shift, comment);
              modal.classList.remove('show');
          };
          
          // エスケープキーでモーダルを閉じる
          document.addEventListener('keydown', function(e) {
              if (e.key === 'Escape') {
                  modal.classList.remove('show');
              }
          });
          
          // モーダル外クリックで閉じる
          modal.onclick = function(e) {
              if (e.target === modal) {
                  modal.classList.remove('show');
              }
          };
      };
      
      // モーダルを閉じる
      vm.hideCommentModal = function() {
          console.log('=== hideCommentModal called ===');
          const modal = document.getElementById('comment-modal-view');
          if (modal) {
              console.log('Closing modal');
              modal.style.display = 'none';
              modal.style.visibility = 'hidden';
              modal.style.opacity = '0';
              modal.classList.remove('show');
          }
      };
      
      // シフト参加を実際に実行
      vm.submitJoinShift = function(shift, comment) {
          console.log('Joining shift:', shift.id, 'with comment:', comment);
          
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
                  vm.showAlert(data.message || 'シフトに参加しました！', 'success');
                  vm.hideCommentModal(); // モーダルを閉じる
                  
                  // データを再読み込み
                  vm.loadShiftDetail();
                  
                  // さらに確実にデータを更新するため、少し遅れて再度読み込み
                  setTimeout(function() {
                      vm.loadShiftDetail();
                      // 定員状況を更新
                      setTimeout(function() {
                          vm.updateCapacityDisplay();
                      }, 100);
                      console.log('=== 参加後のデータ再読み込み完了 ===');
                      console.log('isParticipating after join:', vm.isParticipating());
                      console.log('participants after join:', vm.participants());
                  }, 500);
                  
                  // シフト一覧ページのデータも更新
                  if (typeof window.refreshShiftList === 'function') {
                      window.refreshShiftList();
                  }
                  
                  // 自分のシフトページのデータも更新
                  if (typeof window.refreshMyShifts === 'function') {
                      window.refreshMyShifts();
                  }
                  
                  // 詳細ページに留まる（自動遷移を削除）
              } else {
                  // エラーメッセージを詳細に処理
                  let errorMessage = '参加に失敗しました';
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
              vm.hideCommentModal(); // エラー時もモーダルを閉じる
          })
          .catch(function(error) {
              console.error('Join error:', error);
              vm.showAlert('参加に失敗しました: ' + error.message, 'error');
              vm.hideCommentModal(); // エラー時もモーダルを閉じる
          });
      };
      
      // シフト参加を取消
      vm.cancelParticipation = function(shiftId) {
          console.log('Canceling participation for shift:', shiftId);
          const API = window.API_BASE || '/api';
          fetch(`${API}/shifts/${shiftId}/cancel`, {
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
              if (data.ok) {
                  vm.showAlert('シフト参加を取消しました', 'success');
                  
                  // データを再読み込み
                  vm.loadShiftDetail();
                  
                  // さらに確実にデータを更新するため、少し遅れて再度読み込み
                  setTimeout(function() {
                      vm.loadShiftDetail();
                      // 定員状況を更新
                      setTimeout(function() {
                          vm.updateCapacityDisplay();
                      }, 100);
                      vm.confirmationShown = false; // 確認フラグをリセット
                      console.log('=== 取消後のデータ再読み込み完了 ===');
                      console.log('isParticipating after cancel:', vm.isParticipating());
                      console.log('participants after cancel:', vm.participants());
                  }, 500);
                  
                  // シフト一覧ページのデータも更新
                  if (typeof window.refreshShiftList === 'function') {
                      window.refreshShiftList();
                  }
                  
                  // 自分のシフトページのデータも更新
                  if (typeof window.refreshMyShifts === 'function') {
                      window.refreshMyShifts();
                  }
                  
                  // 詳細ページに留まる（自動遷移を削除）
              } else {
                  vm.showAlert('取消に失敗しました: ' + data.message, 'error');
              }
          })
          .catch(function(error) {
              console.error('Cancel error:', error);
              vm.showAlert('取消に失敗しました', 'error');
          });
      };
      
      // 編集モーダルを表示
      vm.showEditModal = function(shift) {
          console.log('=== showEditModal called ===');
          console.log('showEditModal called with shift:', shift);
          
          const modal = document.getElementById('edit-shift-modal');
          console.log('Edit modal element found:', !!modal);
          
          if (!modal) {
              console.error('edit-shift-modal element not found!');
              return;
          }
          
          // フォームフィールドに現在の値を設定
          document.getElementById('edit-shift-date').value = shift.shift_date || '';
          document.getElementById('edit-start-time').value = shift.start_time || '';
          document.getElementById('edit-end-time').value = shift.end_time || '';
          document.getElementById('edit-recruit-count').value = shift.recruit_count || 1;
          document.getElementById('edit-free-text').value = shift.free_text || '';
          
          // モーダルを表示
          modal.style.cssText = 'position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background: rgba(0,0,0,0.8) !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important;';
          
          // イベントリスナーを設定
          const cancelBtn = document.getElementById('edit-shift-cancel-btn');
          const submitBtn = document.getElementById('edit-shift-submit-btn');
          const form = document.getElementById('edit-shift-form');
          
          // キャンセルボタンのイベント
          cancelBtn.onclick = function() {
              vm.hideEditModal();
          };
          
          // フォーム送信のイベント
          form.onsubmit = function(e) {
              e.preventDefault();
              vm.submitEditShift(shift);
          };
          
          // エスケープキーでモーダルを閉じる
          const escapeHandler = function(e) {
              if (e.key === 'Escape') {
                  vm.hideEditModal();
                  document.removeEventListener('keydown', escapeHandler);
              }
          };
          document.addEventListener('keydown', escapeHandler);
          
          // モーダル外クリックで閉じる
          modal.onclick = function(e) {
              if (e.target === modal) {
                  vm.hideEditModal();
              }
          };
          
          console.log('Edit modal displayed successfully');
      };
      
      // 編集モーダルを閉じる
      vm.hideEditModal = function() {
          console.log('=== hideEditModal called ===');
          const modal = document.getElementById('edit-shift-modal');
          if (modal) {
              modal.style.display = 'none';
              console.log('Edit modal closed');
          }
      };
      
      // シフト編集を実行
      vm.submitEditShift = function(shift) {
          console.log('=== submitEditShift called ===');
          
          const form = document.getElementById('edit-shift-form');
          const formData = new FormData(form);
          
          // 時刻の妥当性チェック
          const startTime = formData.get('start_time');
          const endTime = formData.get('end_time');
          
          if (startTime && endTime && startTime >= endTime) {
              alert('終了時刻は開始時刻より後に設定してください');
              return;
          }
          
          // 送信ボタンを無効化
          const submitBtn = document.getElementById('edit-shift-submit-btn');
          const originalText = submitBtn.textContent;
          submitBtn.disabled = true;
          submitBtn.textContent = '更新中...';
          
          // APIエンドポイントを構築
          const API = window.API_BASE || '/api';
          const url = `${API}/shifts/${shift.id}`;
          
          // フォームデータをJSONに変換
          const jsonData = {
              shift_date: formData.get('shift_date'),
              start_time: formData.get('start_time'),
              end_time: formData.get('end_time'),
              recruit_count: parseInt(formData.get('recruit_count')),
              free_text: formData.get('free_text')
          };
          
          console.log('Sending edit data:', jsonData);
          
          fetch(url, {
              method: 'PUT',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              },
              body: JSON.stringify(jsonData)
          })
          .then(function(response) {
              return response.json();
          })
          .then(function(data) {
              console.log('Edit response:', data);
              
              if (data.ok || data.success) {
                  vm.showAlert('シフト情報を更新しました', 'success');
                  vm.hideEditModal();
                  
                  // 編集完了後、シフト一覧ページにリダイレクト
                  setTimeout(function() {
                      window.location.href = '/shifts';
                  }, 1500); // 成功メッセージを表示してからリダイレクト
                  
              } else {
                  let errorMessage = 'シフト情報の更新に失敗しました';
                  if (data.message) {
                      errorMessage = data.message;
                  } else if (data.error) {
                      errorMessage = data.error;
                  }
                  vm.showAlert(errorMessage, 'error');
              }
          })
          .catch(function(error) {
              console.error('Edit error:', error);
              vm.showAlert('シフト情報の更新に失敗しました: ' + error.message, 'error');
          })
          .finally(function() {
              // 送信ボタンを再有効化
              submitBtn.disabled = false;
              submitBtn.textContent = originalText;
          });
      };
      
      // 戻るボタン
      vm.goBack = function() {
          window.location.href = '/shifts';
      };
      
  }
  
  // 2) シングルトンな VM を使う
  window.__shiftVM = window.__shiftVM || new ShiftDetailViewModel();
  
  // グローバルな更新関数を登録
  window.refreshShiftDetail = function() {
      console.log('Refreshing shift detail from external call');
      if (window.__shiftVM && window.__shiftVM.loadShiftDetail) {
          window.__shiftVM.loadShiftDetail();
      }
  };
  
  // Knockout.jsのバインディングを適用
  document.addEventListener('DOMContentLoaded', function() {
    const root = document.getElementById('shift-detail-root');
    if (root && window.ko) {
      try {
        ko.applyBindings(window.__shiftVM, root);
        bindingApplied = true;
        console.log('Knockout binding applied successfully');
        
        
      } catch (e) {
        console.error('Knockout binding error:', e);
      }
    }
    
    // フォールバック：Knockout.jsのバインディングが失敗した場合の直接イベントリスナー
    setTimeout(function() {
      const participateBtn = document.querySelector('.btn-participate');
      console.log('Looking for participate button:', participateBtn);
      
      if (participateBtn) {
        console.log('Participate button found, checking if already processed:', participateBtn.hasAttribute('data-bind-processed'));
        
        if (!participateBtn.hasAttribute('data-bind-processed')) {
          console.log('Adding fallback click listener to participate button');
          
          // ボタンの詳細な状態を確認
          const rect = participateBtn.getBoundingClientRect();
          const computedStyle = getComputedStyle(participateBtn);
          console.log('Button detailed state:', {
            element: participateBtn,
            rect: rect,
            computedStyle: {
              display: computedStyle.display,
              visibility: computedStyle.visibility,
              opacity: computedStyle.opacity,
              pointerEvents: computedStyle.pointerEvents,
              position: computedStyle.position,
              zIndex: computedStyle.zIndex,
              width: computedStyle.width,
              height: computedStyle.height,
              top: computedStyle.top,
              left: computedStyle.left
            },
            disabled: participateBtn.disabled,
            hidden: participateBtn.hidden,
            offsetWidth: participateBtn.offsetWidth,
            offsetHeight: participateBtn.offsetHeight,
            clientWidth: participateBtn.clientWidth,
            clientHeight: participateBtn.clientHeight
          });
          
          // ボタンの親要素もチェック
          const parent = participateBtn.parentElement;
          while (parent && parent !== document.body) {
            const parentStyle = getComputedStyle(parent);
            console.log('Parent element:', {
              tag: parent.tagName,
              class: parent.className,
              display: parentStyle.display,
              visibility: parentStyle.visibility,
              opacity: parentStyle.opacity,
              pointerEvents: parentStyle.pointerEvents,
              position: parentStyle.position,
              zIndex: parentStyle.zIndex
            });
            parent = parent.parentElement;
          }
          
          participateBtn.addEventListener('click', function(e) {
            console.log('=== Fallback click handler triggered ===');
            console.log('Event object:', e);
            console.log('Target element:', e.target);
            console.log('Current target:', e.currentTarget);
            
            const vm = window.__shiftVM;
            console.log('ViewModel available:', !!vm);
            console.log('joinShift method available:', !!(vm && vm.joinShift));
            
            if (vm && vm.joinShift) {
              console.log('Calling vm.joinShift() from fallback handler');
              try {
                vm.joinShift();
              } catch (error) {
                console.error('Error calling vm.joinShift():', error);
              }
            } else {
              console.error('ViewModel or joinShift method not available');
              console.log('Available methods on vm:', vm ? Object.keys(vm) : 'vm is null');
            }
          });
          
          // 追加のイベントリスナー（mousedown、mouseupなど）
          participateBtn.addEventListener('mousedown', function(e) {
            console.log('Mouse down on participate button');
          });
          
          participateBtn.addEventListener('mouseup', function(e) {
            console.log('Mouse up on participate button');
          });
          
          participateBtn.setAttribute('data-bind-processed', 'true');
        } else {
          console.log('Participate button already processed, adding additional listener');
          
          // 既に処理済みでも追加のリスナーを設定
          participateBtn.addEventListener('click', function(e) {
            console.log('=== Additional click handler (already processed) ===');
            const vm = window.__shiftVM;
            if (vm && vm.joinShift) {
              console.log('Calling vm.joinShift() from additional handler');
              try {
                vm.joinShift();
              } catch (error) {
                console.error('Error in additional handler:', error);
              }
            }
          });
        }
      } else {
        console.error('Participate button not found!');
        console.log('Available buttons:', document.querySelectorAll('button'));
        console.log('Available elements with btn-participate class:', document.querySelectorAll('.btn-participate'));
      }
    }, 500);
  });
  const vm = window.__shiftVM;
  
  // ===== Helper: Resolve Shift ID from multiple URL patterns or data attribute =====
  function resolveShiftId() {
    // 1) data-id on root: <div id="shift-detail-root" data-shift-id="123">
    const rootEl = document.getElementById('shift-detail-root');
    const dataId = rootEl && rootEl.getAttribute('data-shift-id');
    if (dataId && /^\d+$/.test(dataId)) return dataId;
  
    // 2) /shifts/123 or /shifts/view/123 or /shift/123
    const m = location.pathname.match(/\/shifts?(?:\/view)?\/(\d+)\b/);
    if (m) return m[1];
  
    // 3) query param ?id=123
    const sp = new URLSearchParams(location.search);
    const qid = sp.get('id');
    if (qid && /^\d+$/.test(qid)) return qid;
  
    return null;
  }
  
  // ===== Helper: API URL builder with fallback (/api/shifts/:id or /api/v1/shifts/:id) =====
  const SHIFT_API_BASE = (window.API_BASE || '/api') + '/shifts/';
  const SHIFT_API_FALLBACK = (window.API_BASE || '/api') + '/shifts/';
  function apiUrlFor(id) {
    return SHIFT_API_BASE.replace(/\/+$/,'/') + encodeURIComponent(id);
  }
  function apiFallbackUrlFor(id) {
    return SHIFT_API_FALLBACK.replace(/\/+$/,'/') + encodeURIComponent(id);
  }
  
  // 3) バインディングを適用（データ読み込み後に遅延）
  let bindingApplied = false;
  (function bindOnce() {
    const root = document.getElementById('shift-detail-root') || document.body;
    function bind() {
      try {
        if (window.ko && !bindingApplied) {
          // シフトデータが読み込まれるまで少し待つ
          setTimeout(function() {
            if (!bindingApplied) {
              try {
                ko.applyBindings(vm, root);
                bindingApplied = true;
                console.log('Knockout binding applied ONCE (delayed)');
              } catch (e) {
                console.error('Delayed binding error:', e);
              }
            }
          }, 200);
        }
      } catch (e) {
        console.error('Binding error:', e);
      }
    }
    // DOMContentLoadedイベントは既に処理済みなので、即座に実行
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', bind);
    } else {
      bind();
    }
  })();
  
  // 4) ID を取って "同じ vm" にロードする
  (function initLoad() {
    console.log('initLoad function called');
    const id = resolveShiftId();
    console.log('Resolved shift ID:', id);
    console.log('Initial state - loading:', vm.loading());
    console.log('Initial state - isReady:', vm.isReady());
    console.log('Initial state - shift:', vm.shift());
    
    if (!id) {
      console.error('No shift ID found in URL');
      vm.loading(false);
      vm.error && vm.error('シフトIDがURLから取得できませんでした。');
      return;
    }
    console.log('Loading shift detail for ID:', id);
    // ここで new し直さないこと！！ vm.load を同じインスタンスに対して呼ぶ
    vm.load(id);
  })();