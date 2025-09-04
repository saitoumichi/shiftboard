<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト詳細 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/common.css'); ?>">
    
    <!-- シフト詳細ページ専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shifts-view.css'); ?>">
    
    <!-- Knockout.js -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/knockout-min.js'); ?>"></script>
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
    
    <!-- 共通JavaScript -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/common.js'); ?>"></script>
</head>
<body>
    <!-- ヘッダー -->
    <div class="header">
        <button class="btn-back-header" data-bind="click: $root.goBack">戻る</button>
    </div>
    
    <!-- アラート表示 -->
    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <!-- 左セクション -->
        <div class="left-section">
            <!-- シフト基本情報 -->
            <div class="shift-basic-info">
                <div class="shift-title" data-bind="text: shiftTitle">読み込み中...</div>
                <div class="shift-date" data-bind="text: shiftDate">読み込み中...</div>
                <div class="shift-time" data-bind="text: shiftTime">読み込み中...</div>
                <div class="slot-info" data-bind="text: slotInfo">読み込み中...</div>
                <div class="shift-note" data-bind="text: shiftNote">読み込み中...</div>
                
                <!-- デバッグ情報 -->
                <div style="margin-top: 20px; padding: 10px; background: #f0f0f0; border-radius: 4px; font-size: 12px;">
                    <strong>デバッグ情報:</strong><br>
                    シフトID: <span data-bind="text: shift().id">-</span><br>
                    シフト日付: <span data-bind="text: shift().shift_date">-</span><br>
                    開始時間: <span data-bind="text: shift().start_time">-</span><br>
                    終了時間: <span data-bind="text: shift().end_time">-</span><br>
                    定員数: <span data-bind="text: shift().slot_count">-</span><br>
                    備考: <span data-bind="text: shift().note">-</span>
                </div>
            </div>
            
            <!-- 参加者リスト -->
            <div class="participants-section">
                <h3>参加者一覧</h3>
                <div class="participants-list">
                    <!-- ko if: participants().length > 0 -->
                    <!-- ko foreach: participants -->
                    <div class="participant-item">
                        <div class="participant-icon"></div>
                        <div class="participant-name" data-bind="text: name">参加者名</div>
                        <div class="participant-status" data-bind="text: status">CONFIRMED</div>
                    </div>
                    <!-- /ko -->
                    <!-- /ko -->
                    
                    <!-- 参加者がいない場合 -->
                    <!-- ko if: participants().length === 0 -->
                    <div class="no-participants">
                        参加者がいません
                    </div>
                    <!-- /ko -->
                </div>
                
                <!-- デバッグ情報 -->
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    参加者数: <span data-bind="text: participants().length">0</span><br>
                    シフトID: <span data-bind="text: shift().id">-</span><br>
                    シフトデータ: <span data-bind="text: JSON.stringify(shift())">-</span>
                </div>
            </div>
        </div>
        
        <!-- 右セクション -->
        <div class="right-section">
            <!-- アクションボタン -->
            <div class="action-buttons">
                <button class="action-btn btn-participate" data-bind="click: $root.toggleParticipation">参加/取消</button>
                <button class="action-btn btn-edit" data-bind="click: $root.editShift">編集</button>
            </div>
            
            <!-- 募集中の時刻詳細 -->
            <div class="recruitment-times">
                <h3>募集中の時刻</h3>
                <ul class="recruitment-details">
                    <li>開始時刻: <span data-bind="text: startTime">読み込み中...</span></li>
                    <li>終了時刻: <span data-bind="text: endTime">読み込み中...</span></li>
                    <li>募集締切: <span data-bind="text: deadline">前日18:00</span></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- ローディング表示 -->
    <div data-bind="visible: loading" class="loading">
        読み込み中...
    </div>
    
    <script>
        // Knockout.js ViewModel
        function ShiftDetailViewModel() {
            var self = this;
            
            // データ
            self.shift = ko.observable({
                id: null,
                shift_date: null,
                start_time: null,
                end_time: null,
                note: null,
                slot_count: 0,
                assigned_users: []
            });
            self.participants = ko.observableArray([]);
            self.loading = ko.observable(false);
            self.showRecruitmentDetails = ko.observable(false);
            
            // 計算プロパティ
            self.shiftTitle = ko.computed(function() {
                var shift = self.shift();
                if (shift.note && shift.note.trim() !== '') {
                    return shift.note; // 備考がある場合は備考をタイトルとして使用
                } else {
                    // 備考がない場合は日付を読みやすい形式で表示
                    var dateStr = shift.shift_date || '2025-09-15';
                    if (dateStr && dateStr.length >= 10) {
                        var year = dateStr.substring(0, 4);
                        var month = dateStr.substring(5, 7);
                        var day = dateStr.substring(8, 10);
                        
                        // 月と日の先頭の0を削除
                        month = parseInt(month, 10).toString();
                        day = parseInt(day, 10).toString();
                        
                        return year + '年' + month + '月' + day + '日のシフト';
                    }
                    return dateStr;
                }
            });
            
            self.shiftDate = ko.computed(function() {
                var shift = self.shift();
                var dateStr = shift.shift_date;
                
                console.log('shiftDate computed - shift:', shift, 'dateStr:', dateStr);
                
                if (!dateStr || dateStr === null) {
                    return '読み込み中...';
                }
                
                // 日付を読みやすい形式に変換 (YYYY-MM-DD → YYYY年MM月DD日)
                if (dateStr.length >= 10) {
                    var year = dateStr.substring(0, 4);
                    var month = dateStr.substring(5, 7);
                    var day = dateStr.substring(8, 10);
                    
                    // 月と日の先頭の0を削除
                    month = parseInt(month, 10).toString();
                    day = parseInt(day, 10).toString();
                    
                    return year + '年' + month + '月' + day + '日';
                }
                
                return dateStr;
            });
            
            self.shiftTime = ko.computed(function() {
                var shift = self.shift();
                var startTime = shift.start_time;
                var endTime = shift.end_time;
                
                console.log('shiftTime computed - shift:', shift, 'startTime:', startTime, 'endTime:', endTime);
                
                if (!startTime || !endTime) {
                    return '読み込み中...';
                }
                
                // 秒数を削除（HH:MM:SS → HH:MM）
                if (startTime.length > 5) {
                    startTime = startTime.substring(0, 5);
                }
                if (endTime.length > 5) {
                    endTime = endTime.substring(0, 5);
                }
                
                return startTime + ' - ' + endTime;
            });
            
            self.shiftNote = ko.computed(function() {
                var shift = self.shift();
                var note = shift.note;
                
                if (!note) {
                    return '読み込み中...';
                }
                
                return note.trim() !== '' ? note : '備考なし';
            });
            
            self.slotInfo = ko.computed(function() {
                var shift = self.shift();
                var assigned = self.participants().length;
                var total = shift.slot_count;
                
                if (!total) {
                    return '読み込み中...';
                }
                
                return assigned + '/' + total;
            });
            
            self.availableSlots = ko.computed(function() {
                var shift = self.shift();
                var assigned = self.participants().length;
                var total = shift.slot_count || 4;
                return Math.max(0, total - assigned);
            });
            
            self.totalSlots = ko.computed(function() {
                return self.shift().slot_count || 4;
            });
            
            self.participantCount = ko.computed(function() {
                return self.participants().length;
            });
            
            self.startTime = ko.computed(function() {
                var time = self.shift().start_time || '10:00';
                return time.length > 5 ? time.substring(0, 5) : time;
            });
            
            self.endTime = ko.computed(function() {
                var time = self.shift().end_time || '14:00';
                return time.length > 5 ? time.substring(0, 5) : time;
            });
            
            self.deadline = ko.computed(function() {
                return '前日18:00'; // 固定値
            });
            
            // アラート表示
            self.showAlert = function(message, type) {
                var alert = document.getElementById('alert');
                var alertMessage = document.getElementById('alert-message');
                
                alert.className = 'alert alert-' + type;
                alertMessage.textContent = message;
                alert.style.display = 'block';
                
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 5000);
            };
            
            // シフト詳細を取得
            self.loadShiftDetail = function() {
                self.loading(true);
                
                // URLからシフトIDを取得
                var pathParts = window.location.pathname.split('/');
                var shiftId = pathParts[pathParts.length - 1];
                
                console.log('Loading shift detail for ID:', shiftId);
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shiftId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('API Response:', response);
                        if (response.success) {
                            console.log('Shift data:', response.data);
                            console.log('Assigned users:', response.data.assigned_users);
                            
                            // シフトデータを設定
                            self.shift(response.data);
                            console.log('Shift data set:', self.shift());
                            
                            // 参加者データを設定（配列をクリアしてから追加）
                            self.participants.removeAll();
                            if (response.data.assigned_users && Array.isArray(response.data.assigned_users)) {
                                response.data.assigned_users.forEach(function(user) {
                                    self.participants.push(user);
                                });
                            }
                            
                            console.log('Participants after setting:', self.participants());
                            console.log('Participants count:', self.participants().length);
                            
                            // データ設定後に少し待ってからデバッグ情報を出力
                            setTimeout(function() {
                                console.log('Shift data after timeout:', self.shift());
                                console.log('Shift title:', self.shiftTitle());
                                console.log('Shift date:', self.shiftDate());
                                console.log('Shift time:', self.shiftTime());
                                console.log('Shift note:', self.shiftNote());
                                console.log('Slot info:', self.slotInfo());
                            }, 100);
                        } else {
                            self.showAlert('シフト詳細の取得に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフト詳細の取得に失敗しました', 'error');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        self.loading(false);
                    }
                });
            };
            
            // 募集中の時刻を表示
            self.showRecruitmentTimes = function() {
                self.showRecruitmentDetails(!self.showRecruitmentDetails());
            };
            
            // 参加/取消
            self.toggleParticipation = function() {
                var shift = self.shift();
                var isParticipating = self.participants().some(function(p) {
                    return p.id === 1; // 仮のユーザーID
                });
                
                if (isParticipating) {
                    // 取消
                    if (!confirm('このシフトの参加を取り消しますか？')) {
                        return;
                    }
                    
                    $.ajax({
                        url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shift.id + '/cancel',
                        type: 'POST',
                        data: {
                            csrf_token: 'dummy_token'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                self.showAlert('シフトの参加を取り消しました', 'success');
                                self.loadShiftDetail();
                            } else {
                                self.showAlert('シフトの取消に失敗しました: ' + response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            self.showAlert('シフトの取消に失敗しました', 'error');
                            console.error('Error:', error);
                        }
                    });
                } else {
                    // 参加
                    $.ajax({
                        url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shift.id + '/join',
                        type: 'POST',
                        data: {
                            csrf_token: 'dummy_token'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                self.showAlert('シフトに参加しました', 'success');
                                self.loadShiftDetail();
                            } else {
                                self.showAlert('シフトの参加に失敗しました: ' + response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            self.showAlert('シフトの参加に失敗しました', 'error');
                            console.error('Error:', error);
                        }
                    });
                }
            };
            
            // シフト編集
            self.editShift = function() {
                var shift = self.shift();
                if (!shift) return;
                
                // 編集フォームの値を設定
                var newDate = prompt('シフト日付 (YYYY-MM-DD):', shift.shift_date);
                if (!newDate) return;
                
                var newStartTime = prompt('開始時刻 (HH:MM):', shift.start_time);
                if (!newStartTime) return;
                
                var newEndTime = prompt('終了時刻 (HH:MM):', shift.end_time);
                if (!newEndTime) return;
                
                var newSlotCount = prompt('定員数:', shift.slot_count);
                if (!newSlotCount) return;
                
                var newNote = prompt('備考:', shift.note || '');
                
                // バリデーション
                if (newStartTime >= newEndTime) {
                    self.showAlert('終了時間は開始時間より後にしてください', 'error');
                    return;
                }
                
                if (parseInt(newSlotCount) < 1) {
                    self.showAlert('定員数は1以上である必要があります', 'error');
                    return;
                }
                
                // 現在の参加者数をチェック
                if (parseInt(newSlotCount) < self.participants().length) {
                    self.showAlert('定員数を現在の参加者数より少なくすることはできません', 'error');
                    return;
                }
                
                // シフト更新
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shift.id + '/update',
                    type: 'POST',
                    data: {
                        shift_date: newDate,
                        start_time: newStartTime,
                        end_time: newEndTime,
                        slot_count: newSlotCount,
                        note: newNote,
                        csrf_token: 'dummy_token'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('シフトを更新しました', 'success');
                            self.loadShiftDetail();
                        } else {
                            self.showAlert('シフトの更新に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフトの更新に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // シフト削除
            self.deleteShift = function() {
                var shift = self.shift();
                if (!shift) return;
                
                if (!confirm('このシフトを削除しますか？\n\n注意: 参加者がいる場合は削除できません。')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shift.id + '/delete',
                    type: 'POST',
                    data: {
                        csrf_token: 'dummy_token'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showAlert('シフトを削除しました', 'success');
                            // 削除後は一覧画面に戻る
                            setTimeout(function() {
                                window.location.href = '<?php echo \Fuel\Core\Uri::create('shifts'); ?>';
                            }, 1500);
                        } else {
                            self.showAlert('シフトの削除に失敗しました: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showAlert('シフトの削除に失敗しました', 'error');
                        console.error('Error:', error);
                    }
                });
            };
            
            // 戻る
            self.goBack = function() {
                window.location.href = '<?php echo \Fuel\Core\Uri::create('shifts'); ?>';
            };
            
            // 初期化
            self.loadShiftDetail();
        }
        
        // ViewModelを適用
        try {
            ko.applyBindings(new ShiftDetailViewModel());
            console.log('Knockout.js binding applied successfully');
        } catch (error) {
            console.error('Knockout.js binding error:', error);
        }
    </script>
</body>
</html>
