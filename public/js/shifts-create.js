// シフト作成ページ用JavaScript

// 未ログインガード
if (!window.CURRENT_USER_ID) {
  alert('ログインが必要です');
  location.href = '/';
}

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
            shift_date: $('#shift_date').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
            recruit_count: Number($('#recruit_count').val() || 1),
            free_text: $('#note').val() || ''
        };
        
        // デバッグ用：送信データをコンソールに出力
        console.log('Form data being sent:', formData);
        
        // バリデーション
        if (!formData.shift_date || !formData.start_time || !formData.end_time) {
            showAlert('すべての必須項目を入力してください。', 'error');
            return;
        }
        
        if (formData.recruit_count < 1) {
            showAlert('募集人数は1以上で入力してください。', 'error');
            return;
        }
        
        if (formData.start_time >= formData.end_time) {
            showAlert('終了時間は開始時間より後にしてください。', 'error');
            return;
        }
        
        // AJAX送信
        $.ajax({
            url: `${window.API_BASE}/shifts`,
            method: 'POST',
            contentType: 'application/json',
            processData: false,
            data: JSON.stringify({
              shift_date: ($('#shift_date').val() || '').trim(),
              start_time: ($('#start_time').val() || '').trim(),
              end_time:   ($('#end_time').val() || '').trim(),
              recruit_count: Number($('#recruit_count').val() || 1),
              free_text:   ($('#note').val() || '').trim()
            }),
            dataType: 'json',
            success: function(response) {
                if (response.ok || response.success) {
                    showAlert('シフトが正常に作成されました。', 'success');
                    setTimeout(function() {
                        window.location.href = '/shifts';
                    }, 2000);
                } else {
                    var errorMsg = response.message || response.error || '不明なエラー';
                    showAlert('シフトの作成に失敗しました: ' + errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'シフトの作成に失敗しました';
                
                try {
                    var response = JSON.parse(xhr.responseText);
                    console.log('Error response:', response);
                    if (response.message) {
                        errorMessage = response.message;
                    } else if (response.error) {
                        errorMessage = response.error;
                    } else if (response.errors) {
                        // バリデーションエラーの場合
                        var errorList = [];
                        for (var field in response.errors) {
                            errorList.push(field + ': ' + response.errors[field]);
                        }
                        errorMessage = errorList.join(', ');
                    }
                } catch (e) {
                    console.log('JSON parse error:', e);
                    // JSON解析に失敗した場合はデフォルトメッセージを使用
                }
                
                if (xhr.status === 400) {
                    errorMessage = '入力データに問題があります: ' + errorMessage;
                } else if (xhr.status === 500) {
                    errorMessage = 'サーバーエラーが発生しました: ' + errorMessage;
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
