<?php use Fuel\Core\Uri; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>自分のシフト - ShiftBoard</title>
    
    <!-- ユーザーIDとAPIベースURL設定 -->
    <meta name="current-user-id" content="<?= (int)($current_user_id ?? 0) ?>">
    <meta name="api-base" content="/api">
    <script>
        window.API_BASE = '/api';
        window.CURRENT_USER_ID = Number(
            document.querySelector('meta[name="current-user-id"]')?.content || 0
        );
    </script>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>">
    
    <!-- jQuery -->
    <script src="<?= Uri::create('js/jquery-3.6.0.min.js') ?>"></script>
    
    <script src="<?= Uri::create('js/knockout-min.js') ?>"></script>

    <!-- 共通JavaScript -->
    <script src="<?= Uri::create('js/common.js') ?>"></script>
</head>
<body>
    <div class="page-container">
        <!-- ページヘッダー -->
        <div class="page-header">
            <div class="page-header-inner">
                <h1 class="page-title">自分のシフト</h1>
                <div class="page-actions">
                    <a href="<?= Uri::create('shifts'); ?>" class="btn btn-secondary">← シフト一覧へ</a>
                </div>
            </div>
        </div>

        <!-- メインコンテンツ -->
        <div class="main-content">
            <div id="my-shifts-container">
                <!-- 自分のシフト一覧がここに表示されます -->
            </div>
            
            <div id="no-shifts-message" class="no-data" style="display: none;">
                <p>参加中のシフトはありません</p>
            </div>
        </div>
    </div>

    <script>
        // 未ログインガード
        if (!window.CURRENT_USER_ID) {
            alert('ログインが必要です');
            location.href = '/';
        }

        // 自分のシフト一覧を読み込み
        function loadMyShifts() {
            $.ajax({
                url: window.API_BASE + '/shifts',
                type: 'GET',
                data: {
                    mine: 1,
                    user_id: window.CURRENT_USER_ID
                },
                dataType: 'json',
                success: function(response) {
                    if (response.ok && response.data) {
                        renderMyShifts(response.data);
                    } else {
                        showNoShiftsMessage();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading my shifts:', error);
                    showNoShiftsMessage();
                }
            });
        }

        // 自分のシフト一覧をレンダリング
        function renderMyShifts(shifts) {
            var container = document.getElementById('my-shifts-container');
            var noShiftsMessage = document.getElementById('no-shifts-message');
            
            if (!container) return;
            
            container.innerHTML = '';
            
            if (shifts.length === 0) {
                showNoShiftsMessage();
                return;
            }
            
            if (noShiftsMessage) {
                noShiftsMessage.style.display = 'none';
            }
            
            shifts.forEach(function(shift) {
                var shiftItem = document.createElement('div');
                shiftItem.className = 'shift-item';
                
                var dateDiv = document.createElement('div');
                dateDiv.className = 'shift-date';
                dateDiv.textContent = shift.shift_date;
                
                var timeDiv = document.createElement('div');
                timeDiv.className = 'shift-time';
                timeDiv.textContent = shift.start_time + ' - ' + shift.end_time;
                
                var statusDiv = document.createElement('div');
                statusDiv.className = 'shift-status';
                statusDiv.textContent = '参加中';
                
                // コメント表示（あれば）
                var commentDiv = document.createElement('div');
                commentDiv.className = 'shift-comment';
                if (shift.self_word && shift.self_word.trim()) {
                    commentDiv.textContent = 'コメント: ' + shift.self_word;
                    commentDiv.style.cssText = 'font-style: italic; color: #666; margin-top: 4px; font-size: 0.9em;';
                } else {
                    commentDiv.style.display = 'none';
                }
                
                var actionsDiv = document.createElement('div');
                actionsDiv.className = 'shift-actions';
                
                var detailBtn = document.createElement('button');
                detailBtn.className = 'btn btn-primary';
                detailBtn.textContent = '詳細';
                detailBtn.onclick = function() {
                    window.location.href = '/shifts/' + shift.id;
                };
                
                var cancelBtn = document.createElement('button');
                cancelBtn.className = 'btn btn-danger';
                cancelBtn.textContent = 'キャンセル';
                cancelBtn.onclick = function() {
                    if (confirm('このシフトの参加を取り消しますか？')) {
                        cancelShift(shift.id);
                    }
                };
                
                actionsDiv.appendChild(detailBtn);
                actionsDiv.appendChild(cancelBtn);
                
                shiftItem.appendChild(dateDiv);
                shiftItem.appendChild(timeDiv);
                shiftItem.appendChild(statusDiv);
                shiftItem.appendChild(commentDiv);
                shiftItem.appendChild(actionsDiv);
                
                container.appendChild(shiftItem);
            });
        }

        // シフトなしメッセージを表示
        function showNoShiftsMessage() {
            var noShiftsMessage = document.getElementById('no-shifts-message');
            if (noShiftsMessage) {
                noShiftsMessage.style.display = 'block';
            }
        }

        // シフトキャンセル
        function cancelShift(shiftId) {
            $.ajax({
                url: window.API_BASE + '/shifts/' + shiftId + '/cancel',
                type: 'POST',
                contentType: 'application/json; charset=UTF-8',
                data: JSON.stringify({ user_id: window.CURRENT_USER_ID }),
                dataType: 'json',
                success: function(response) {
                    if (response.ok) {
                        alert('シフトの参加を取り消しました');
                        loadMyShifts(); // 一覧を再読み込み
                    } else {
                        alert('キャンセルに失敗しました: ' + (response.message || 'エラーが発生しました'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('キャンセルに失敗しました');
                    console.error('Error:', error);
                }
            });
        }

        // ページ読み込み時に自分のシフトを読み込み
        $(document).ready(function() {
            loadMyShifts();
        });
    </script>

    <style>
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .page-header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            margin: 0;
            color: #333;
        }
        
        .main-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .shift-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
        
        .shift-date {
            font-weight: bold;
            color: #333;
            min-width: 120px;
        }
        
        .shift-time {
            color: #666;
            min-width: 150px;
        }
        
        .shift-status {
            color: #2e7d32;
            font-weight: bold;
            min-width: 80px;
        }
        
        .shift-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
    </style>
</body>
</html>
