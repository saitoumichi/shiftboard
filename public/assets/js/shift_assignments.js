$(document).ready(function() {
    // グローバル変数
    var assignments = [];
    var currentFilters = {};
    
    // 初期化
    initializePage();
    
    function initializePage() {
        loadAssignments();
        loadUsers();
        setupEventListeners();
    }
    
    function setupEventListeners() {
        // フィルターボタン
        $('#apply-filters').on('click', applyFilters);
        $('#clear-filters').on('click', clearFilters);
        
        // アクションボタン
        $('#export-btn').on('click', exportAssignments);
        $('#refresh-btn').on('click', loadAssignments);
        
        // モーダル
        $('.modal-close').on('click', closeModal);
        $('#edit-assignment').on('click', editAssignment);
        $('#cancel-assignment').on('click', cancelAssignment);
        
        // 割り当てアイテムクリック
        $(document).on('click', '.assignment-item', function() {
            var assignmentId = $(this).data('assignment-id');
            showAssignmentDetails(assignmentId);
        });
    }
    
    function loadAssignments() {
        showLoading(true);
        
        $.ajax({
            url: '/api/shift_assignments',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    assignments = response.data || [];
                    updateStatistics();
                    renderAssignments();
                } else {
                    showError('割り当てデータの読み込みに失敗しました: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('割り当てデータの読み込みに失敗しました');
                console.error('AJAX Error:', error);
            },
            complete: function() {
                showLoading(false);
            }
        });
    }
    
    function loadUsers() {
        $.ajax({
            url: '/api/users',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var userSelect = $('#member-filter');
                    userSelect.empty().append('<option value="">すべてのユーザー</option>');
                    
                    response.data.forEach(function(user) {
                        userSelect.append('<option value="' + user.id + '">' + user.name + '</option>');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Users loading error:', error);
            }
        });
    }
    
    function updateStatistics() {
        var total = assignments.length;
        var monthly = getCurrentMonthAssignments().length;
        var unassigned = getUnassignedShifts().length;
        var full = getFullShifts().length;
        
        $('#total-assignments').text(total);
        $('#monthly-assignments').text(monthly);
        $('#unassigned-shifts').text(unassigned);
        $('#full-shifts').text(full);
    }
    
    function getCurrentMonthAssignments() {
        var currentDate = new Date();
        var currentMonth = currentDate.getMonth();
        var currentYear = currentDate.getFullYear();
        
        return assignments.filter(function(assignment) {
            var assignmentDate = new Date(assignment.shift_date);
            return assignmentDate.getMonth() === currentMonth && 
                   assignmentDate.getFullYear() === currentYear;
        });
    }
    
    function getUnassignedShifts() {
        // この実装は実際のシフトデータに依存します
        return [];
    }
    
    function getFullShifts() {
        return assignments.filter(function(assignment) {
            return assignment.assigned_users && 
                   assignment.assigned_users.length >= assignment.slot_count;
        });
    }
    
    function renderAssignments() {
        var container = $('#assignments-list');
        container.empty();
        
        var filteredAssignments = filterAssignments(assignments);
        
        if (filteredAssignments.length === 0) {
            $('#no-data').show();
            return;
        }
        
        $('#no-data').hide();
        
        filteredAssignments.forEach(function(assignment) {
            var assignmentHtml = createAssignmentHtml(assignment);
            container.append(assignmentHtml);
        });
    }
    
    function createAssignmentHtml(assignment) {
        var statusClass = assignment.status || 'assigned';
        var statusText = getStatusText(assignment.status);
        var assignedCount = assignment.assigned_users ? assignment.assigned_users.length : 0;
        var slotCount = assignment.slot_count || 1;
        
        return `
            <div class="assignment-item" data-assignment-id="${assignment.id}">
                <div class="assignment-header">
                    <div class="assignment-title">${assignment.title || '無題のシフト'}</div>
                    <span class="assignment-status ${statusClass}">${statusText}</span>
                </div>
                <div class="assignment-details">
                    <div class="assignment-detail">
                        <strong>日付:</strong>
                        <span>${formatDate(assignment.shift_date)}</span>
                    </div>
                    <div class="assignment-detail">
                        <strong>時間:</strong>
                        <span>${assignment.start_time} - ${assignment.end_time}</span>
                    </div>
                    <div class="assignment-detail">
                        <strong>割り当て:</strong>
                        <span>${assignedCount}/${slotCount}人</span>
                    </div>
                    <div class="assignment-detail">
                        <strong>作成日:</strong>
                        <span>${formatDate(assignment.created_at)}</span>
                    </div>
                </div>
            </div>
        `;
    }
    
    function filterAssignments(assignments) {
        return assignments.filter(function(assignment) {
            // ステータスフィルター
            if (currentFilters.status && assignment.status !== currentFilters.status) {
                return false;
            }
            
            // メンバーフィルター
            if (currentFilters.member) {
                var hasMember = assignment.assigned_users && 
                               assignment.assigned_users.some(function(user) {
                                   return user.user_id == currentFilters.member;
                               });
                if (!hasMember) {
                    return false;
                }
            }
            
            // 日付フィルター
            if (currentFilters.dateFrom) {
                var assignmentDate = new Date(assignment.shift_date);
                var fromDate = new Date(currentFilters.dateFrom);
                if (assignmentDate < fromDate) {
                    return false;
                }
            }
            
            if (currentFilters.dateTo) {
                var assignmentDate = new Date(assignment.shift_date);
                var toDate = new Date(currentFilters.dateTo);
                if (assignmentDate > toDate) {
                    return false;
                }
            }
            
            return true;
        });
    }
    
    function applyFilters() {
        currentFilters = {
            status: $('#status-filter').val(),
            user: $('#member-filter').val(),
            dateFrom: $('#date-from').val(),
            dateTo: $('#date-to').val()
        };
        
        renderAssignments();
    }
    
    function clearFilters() {
        $('#status-filter').val('');
        $('#member-filter').val('');
        $('#date-from').val('');
        $('#date-to').val('');
        
        currentFilters = {};
        renderAssignments();
    }
    
    function showAssignmentDetails(assignmentId) {
        var assignment = assignments.find(function(a) { return a.id == assignmentId; });
        
        if (!assignment) {
            showError('割り当てが見つかりません');
            return;
        }
        
        var detailsHtml = createAssignmentDetailsHtml(assignment);
        $('#assignment-details').html(detailsHtml);
        $('#assignment-modal').show();
    }
    
    function createAssignmentDetailsHtml(assignment) {
        var assignedUsers = assignment.assigned_users || [];
        var usersHtml = assignedUsers.map(function(user) {
            return `<div class="assigned-user">${user.name} (${getStatusText(user.status)})</div>`;
        }).join('');
        
        return `
            <div class="assignment-detail-full">
                <h4>${assignment.title || '無題のシフト'}</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>日付:</strong>
                        <span>${formatDate(assignment.shift_date)}</span>
                    </div>
                    <div class="detail-item">
                        <strong>時間:</strong>
                        <span>${assignment.start_time} - ${assignment.end_time}</span>
                    </div>
                    <div class="detail-item">
                        <strong>定員:</strong>
                        <span>${assignment.slot_count}人</span>
                    </div>
                    <div class="detail-item">
                        <strong>ステータス:</strong>
                        <span class="assignment-status ${assignment.status}">${getStatusText(assignment.status)}</span>
                    </div>
                </div>
                
                <h5>割り当てメンバー</h5>
                <div class="assigned-users">
                    ${usersHtml || '<div class="no-users">割り当てメンバーがいません</div>'}
                </div>
                
                ${assignment.note ? `<div class="assignment-note"><strong>備考:</strong><br>${assignment.note}</div>` : ''}
            </div>
        `;
    }
    
    function editAssignment() {
        // 編集機能の実装
        showMessage('編集機能は実装予定です');
    }
    
    function cancelAssignment() {
        // キャンセル機能の実装
        if (confirm('この割り当てをキャンセルしますか？')) {
            showMessage('キャンセル機能は実装予定です');
        }
    }
    
    function exportAssignments() {
        // エクスポート機能の実装
        showMessage('エクスポート機能は実装予定です');
    }
    
    function closeModal() {
        $('#assignment-modal').hide();
    }
    
    function showLoading(show) {
        if (show) {
            $('#loading-indicator').show();
            $('#assignments-list').hide();
            $('#no-data').hide();
        } else {
            $('#loading-indicator').hide();
            $('#assignments-list').show();
        }
    }
    
    function showError(message) {
        alert('エラー: ' + message);
    }
    
    function showMessage(message) {
        alert(message);
    }
    
    function getStatusText(status) {
        var statusTexts = {
            'assigned': '割り当て済み',
            'confirmed': '確定済み',
            'cancelled': 'キャンセル'
        };
        return statusTexts[status] || status || '不明';
    }
    
    function formatDate(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        return date.toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }
});
