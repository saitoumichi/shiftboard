<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="current-user-id" content="<?= (int)($current_user_id ?? 0) ?>">
    <title>自分のシフト - Shiftboard</title>
    <link rel="stylesheet" href="/css/shifts.css">
    <script src="/js/knockout-3.5.1.js"></script>
</head>
<body>
    <?php echo View::forge('template'); ?>
    
    <div id="my-shifts-root" class="container">
        <h1 style="margin: 20px 0; color: #333;">📅 自分のシフト</h1>
        
        <!-- ローディング表示 -->
        <div data-bind="visible: loading" style="text-align: center; padding: 40px;">
            <p style="font-size: 18px; color: #666;">読み込み中...</p>
        </div>
        
        <!-- エラー表示 -->
        <div data-bind="visible: error() !== '', text: error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0;"></div>
        
        <!-- 自分が参加しているシフト一覧 -->
        <div data-bind="visible: !loading() && error() === ''">
            <div data-bind="if: myShifts().length === 0" style="text-align: center; padding: 40px; color: #666;">
                <p style="font-size: 18px;">参加しているシフトはありません</p>
                <a href="/shifts" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px;">シフト一覧へ</a>
            </div>
            
            <div data-bind="foreach: myShifts" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; padding: 20px 0;">
                <div class="shift-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" data-bind="click: function() { window.location.href = '/shifts/' + id; }">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div>
                            <div style="font-size: 14px; color: #666; margin-bottom: 5px;" data-bind="text: shift_date"></div>
                            <div style="font-size: 18px; font-weight: bold; color: #333;" data-bind="text: start_time.substring(0,5) + ' 〜 ' + end_time.substring(0,5)"></div>
                        </div>
                        <div style="padding: 6px 12px; background: #e8f5e8; color: #2d5a2d; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            参加中
                        </div>
                    </div>
                    
                    <div style="margin: 15px 0; padding: 12px; background: #f8f9fa; border-radius: 6px;">
                        <div style="font-size: 14px; color: #555;" data-bind="text: free_text || '詳細なし'"></div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                        <div style="font-size: 14px; color: #666;">
                            <span style="font-weight: bold; color: #333;" data-bind="text: assigned_count"></span> / <span data-bind="text: recruit_count"></span> 人
                        </div>
                        <div style="flex: 1;"></div>
                        <button onclick="event.stopPropagation(); if(confirm('このシフトの参加を取り消しますか？')) { cancelShift(this); }" data-bind="attr: { 'data-shift-id': id }" style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; transition: background 0.2s;">
                            取消
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/shifts-my.js"></script>
    <script>
        function cancelShift(button) {
            const shiftId = button.getAttribute('data-shift-id');
            if (!shiftId) return;
            
            fetch('/api/shifts/' + shiftId + '/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success || data.ok) {
                    alert('シフトの参加を取り消しました');
                    location.reload();
                } else {
                    alert('取消に失敗しました: ' + (data.message || '不明なエラー'));
                }
            })
            .catch(function(error) {
                console.error('Cancel error:', error);
                alert('取消に失敗しました');
            });
        }
    </script>
</body>
</html>
