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
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= Uri::create('css/myshifts.css') ?>?v=<?= time() ?>">
    
    <!-- 半透明効果CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/transparent-override.css') ?>?v=<?= time() ?>">
    
    <!-- jQuery -->
    <script src="<?= Uri::create('js/jquery-3.6.0.min.js') ?>" defer></script>
    
    <script src="<?= Uri::create('js/knockout-min.js') ?>" defer></script>

    <!-- 共通JavaScript -->
    <script src="<?= Uri::create('js/common.js') ?>" defer></script>
</head>
<body>
    <!-- 自然の要素 -->
    <div class="sakura-petals">
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌿</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌱</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌿</div>
        <div class="sakura-petal">🍃</div>
        <div class="sakura-petal">🌱</div>
    </div>
    
    <!-- ヘッダー -->
    <div class="myshifts-header">
        <div class="header-left">
            <span class="header-id">自分のシフト</span>
        </div>
        <!-- <div class="header-right">
            <button class="csv-export-btn">CSV000000</button>
        </div> -->
    </div>

    <!-- コントロールパネル -->
    <div class="myshifts-controls">
        <div class="controls-left">
        <span class="current-month" id="current-date-display"><?php echo date('Y年n月j日'); ?></span>
                <div class="filter-tags">
                <span class="filter-tag">日</span>
                <span class="filter-tag">週</span>
                <span class="filter-tag">月</span>
            </div>
        </div>
        <div class="controls-right">
            <button onclick="window.location.href='/shifts'">戻る</button>
        </div>
    </div>

    <!-- メインコンテンツ -->
    <div class="myshifts-main">
        <div class="main-content-wrapper">
            <!-- 左側：参加予定シフト一覧 -->
            <div class="shifts-list-section">
                <h3 class="section-title">参加予定シフト一覧</h3>
                <div id="my-shifts-container">
                    <!-- 自分のシフト一覧がここに表示されます -->
                </div>
                
                <div id="no-shifts-message" class="no-data" style="display: none;">
                    <p>参加中のシフトはありません</p>
                </div>
            </div>

        </div>
    </div>

    <!-- ページタイトル -->
  
  <!-- <div class="myshifts-title">
        <h1>自分のシフト</h1>
    </div> -->
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

                    showNoShiftsMessage();
                }
            });
        }

        // 自分のシフト一覧をレンダリング
        function renderMyShifts(shifts) {
            var container = document.getElementById('my-shifts-container');
            if (!container) return;
            
            container.innerHTML = '';
            
            if (shifts && shifts.length > 0) {
                shifts.forEach(function(shift) {
                    var shiftItem = document.createElement('div');
                    shiftItem.className = 'shift-item';
                    
                    // 日付をフォーマット
                    var shiftDate = new Date(shift.shift_date);
                    var formattedDate = shiftDate.getFullYear() + '/' + 
                        String(shiftDate.getMonth() + 1).padStart(2, '0') + '/' + 
                        String(shiftDate.getDate()).padStart(2, '0');
                    
                    // 時間をフォーマット
                    var startTime = shift.start_time.substring(0, 5);
                    var endTime = shift.end_time.substring(0, 5);
                    var timeRange = startTime + '-' + endTime;
                    
                    // コメントまたはフリーテキスト
                    var comment = shift.self_word || shift.free_text || '00000/000';
                    
                    shiftItem.innerHTML = `
                        <div class="shift-date">${formattedDate}</div>
                        <div class="shift-time">${timeRange}</div>
                        <div class="shift-comment">${comment}</div>
                    `;
                    
                    // クリック時にコメントを表示
                    shiftItem.addEventListener('click', function() {
                        displayComment(comment);
                    });
                    
                    container.appendChild(shiftItem);
                });
            } else {
                showNoShiftsMessage();
            }
        }

        // コメントを表示
        function displayComment(comment) {
            var commentDisplay = document.getElementById('comment-display');
            if (commentDisplay) {
                commentDisplay.value = comment;
            }
        }

        // シフトなしメッセージを表示
        function showNoShiftsMessage() {
            var container = document.getElementById('my-shifts-container');
            var noShiftsMessage = document.getElementById('no-shifts-message');
            
            if (!container) return;
            
            container.style.display = 'none';
            if (noShiftsMessage) {
                noShiftsMessage.style.display = 'block';
        }}

        // ページ読み込み時に自分のシフトを読み込み
        // 今日の日付を表示
        function displayCurrentDate() {
            var today = new Date();
            var year = today.getFullYear();
            var month = today.getMonth() + 1;
            var day = today.getDate();
            var dateString = year + '年' + month + '月' + day + '日';
            
            var dateElement = document.getElementById('current-date-display');
            if (dateElement) {
                dateElement.textContent = dateString;
            }
        }
        
        // DOMContentLoadedイベントで実行
        document.addEventListener('DOMContentLoaded', function() {
            displayCurrentDate();
            if (typeof loadMyShifts === 'function') {
                loadMyShifts();
            }
        });
    </script>
</body>
</html>
