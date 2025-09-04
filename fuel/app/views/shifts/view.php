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
        <h1>シフト詳細</h1>
        <p class="subtitle">・ その日のシフトの詳細を確認できる</p>
    </div>
    
    <!-- アクションボタン -->
    <div class="action-buttons">
        <button class="action-btn btn-back" data-bind="click: $root.goBack">戻る</button>
        <button class="action-btn btn-edit" data-bind="click: $root.editShift">編集</button>
        <button class="action-btn btn-delete" data-bind="click: $root.deleteShift">削除</button>
        <button class="action-btn btn-recruitment" data-bind="click: $root.showRecruitmentTimes">募集中の時刻</button>
        <button class="action-btn btn-participate" data-bind="click: $root.toggleParticipation">参加/取消</button>
    </div>
    
    <!-- アラート表示 -->
    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>
    
    <!-- メインコンテンツ -->
    <div class="main-content">
        <!-- 左セクション -->
        <div class="left-section">
            <!-- シフト詳細情報 -->
            <div class="section-card">
                <div class="section-header">シフト詳細</div>
                
                <div class="shift-info">
                    <div class="shift-title" data-bind="text: shiftTitle">シフトタイトル</div>
                    
                    <div class="shift-details">
                        <div class="detail-item">
                            <div class="detail-label">日付</div>
                            <div class="detail-value" data-bind="text: shiftDate">2025/11/15</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">時間</div>
                            <div class="detail-value" data-bind="text: shiftTime">10:00-14:00</div>
                        </div>
                    </div>
                    
                    <div class="slot-info" data-bind="text: slotInfo">2/4</div>
                    
                    <div class="detail-item" style="margin-top: 15px;">
                        <div class="detail-label">備考</div>
                        <div class="detail-value" data-bind="text: shiftNote">シフトに関する備考</div>
                    </div>
                </div>
            </div>
            
            <!-- 参加者リスト -->
            <div class="section-card">
                <div class="section-header">参加者一覧</div>
                
                <div class="participants-list">
                    <!-- ko foreach: participants -->
                    <div class="participant-item">
                        <div class="participant-icon"></div>
                        <div class="participant-name" data-bind="text: name">参加者名</div>
                        <div class="participant-status">CONFIRMED</div>
                    </div>
                    <!-- /ko -->
                    
                    <!-- 参加者がいない場合 -->
                    <div data-bind="visible: participants().length === 0" style="text-align: center; color: #7f8c8d; padding: 20px;">
                        参加者がいません
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右セクション -->
        <div class="right-section">
            <!-- 募集情報 -->
            <div class="section-card">
                <div class="section-header">募集状況</div>
                
                <div class="recruitment-info">
                    <div class="recruitment-numbers">
                        <div class="recruitment-number" data-bind="text: availableSlots">2</div>
                        <div class="recruitment-number" data-bind="text: totalSlots">4</div>
                    </div>
                    
                    <ul class="recruitment-details">
                        <li>空き枠: <span data-bind="text: availableSlots">2</span>名</li>
                        <li>総枠数: <span data-bind="text: totalSlots">4</span>名</li>
                        <li>参加者: <span data-bind="text: participantCount">2</span>名</li>
                    </ul>
                </div>
            </div>
            
            <!-- 募集中の時刻詳細 -->
            <div class="section-card" data-bind="visible: showRecruitmentDetails">
                <div class="section-header">募集中の時刻詳細</div>
                
                <ul class="recruitment-details">
                    <li>開始時刻: <span data-bind="text: startTime">10:00</span></li>
                    <li>終了時刻: <span data-bind="text: endTime">14:00</span></li>
                    <li>募集締切: <span data-bind="text: deadline">前日18:00</span></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- ローディング表示 -->
    <div data-bind="visible: loading" class="loading">
        読み込み中...
    </div>
    
    <!-- ナビゲーションヒント -->
    <div class="navigation-hint">
        ・戻る→シフト一覧
    </div>
    
    <script>
        // Knockout.js ViewModel
        function ShiftDetailViewModel() {
            var self = this;
            
            // データ
            self.shift = ko.observable({});
            self.participants = ko.observableArray([]);
            self.loading = ko.observable(false);
            self.showRecruitmentDetails = ko.observable(false);
            
            // 計算プロパティ
            self.shiftTitle = ko.computed(function() {
                return self.shift().title || 'シフトタイトル';
            });
            
            self.shiftDate = ko.computed(function() {
                return self.shift().shift_date || '2025/11/15';
            });
            
            self.shiftTime = ko.computed(function() {
                var shift = self.shift();
                return (shift.start_time || '10:00') + '-' + (shift.end_time || '14:00');
            });
            
            self.shiftNote = ko.computed(function() {
                return self.shift().note || 'シフトに関する備考';
            });
            
            self.slotInfo = ko.computed(function() {
                var shift = self.shift();
                var assigned = self.participants().length;
                var total = shift.slot_count || 4;
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
                return self.shift().start_time || '10:00';
            });
            
            self.endTime = ko.computed(function() {
                return self.shift().end_time || '14:00';
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
                
                $.ajax({
                    url: '<?php echo \Fuel\Core\Uri::create('api/shifts'); ?>/' + shiftId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.shift(response.data);
                            self.participants(response.data.assigned_users || []);
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
        ko.applyBindings(new ShiftDetailViewModel());
    </script>
</body>
</html>
