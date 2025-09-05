// シフト作成ページ用JavaScript

$(document).ready(function() {
    // URLパラメータから日付を取得
    var urlParams = new URLSearchParams(window.location.search);
    var dateParam = urlParams.get('date');
    
    if (dateParam) {
        $('#shift_date').val(dateParam);
    }
    
    // フォーム送信処理
    $('#shift-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            title: $('#title').val(),
            shift_date: $('#shift_date').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
            slot_count: Number($('#slot_count').val() || 1),
            note: $('#note').val() || ''
        };
        
        // デバッグ用：送信データをコンソールに出力
        console.log('Form data being sent:', formData);
        console.log('Title value:', $('#title').val());
        console.log('Title element exists:', $('#title').length > 0);
        
        // バリデーション
        if (!formData.title || !formData.shift_date || !formData.start_time || !formData.end_time) {
            showAlert('すべての必須項目を入力してください。', 'error');
            return;
        }
        
        if (formData.start_time >= formData.end_time) {
            showAlert('終了時間は開始時間より後にしてください。', 'error');
            return;
        }
        
        // AJAX送信
        $.ajax({
            url: '/api/shifts',
            method: 'POST',
            contentType: 'application/json',
            processData: false,
            data: JSON.stringify({
              title: ($('#title').val() || '').trim(),              // ★これが必須
              shift_date: ($('#shift_date').val() || '').trim(),
              start_time: ($('#start_time').val() || '').trim(),
              end_time:   ($('#end_time').val() || '').trim(),
              slot_count: Number($('#slot_count').val() || 1),
              note:       ($('#note').val() || '').trim()
            }),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('シフトが正常に作成されました。', 'success');
                    setTimeout(function() {
                        window.location.href = '/shifts';
                    }, 2000);
                } else {
                    showAlert('シフトの作成に失敗しました: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'シフトの作成に失敗しました';
                
                if (xhr.status === 400) {
                    errorMessage = '入力データに問題があります';
                } else if (xhr.status === 500) {
                    errorMessage = 'サーバーエラーが発生しました';
                }
                
                showAlert(errorMessage, 'error');
                console.error('AJAX Error:', error, xhr.responseText);
            }
        });
    });
    
    // アラート表示関数
    function showAlert(message, type) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        var alertId = type === 'success' ? 'success-alert' : 'error-alert';
        
        $('#' + alertId).text(message).show();
        
        // 3秒後に自動で非表示
        setTimeout(function() {
            $('#' + alertId).hide();
        }, 3000);
    }
});
