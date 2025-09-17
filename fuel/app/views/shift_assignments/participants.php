<?php use Fuel\Core\Uri; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>シフト参加者一覧 - ShiftBoard</title>
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>">
    
    <!-- 参加者一覧専用CSS -->
    <link rel="stylesheet" href="<?php echo \Fuel\Core\Uri::create('css/shift_assignments.css'); ?>">
    
    <!-- jQuery for AJAX -->
    <script src="<?php echo \Fuel\Core\Uri::create('js/jquery-3.6.0.min.js'); ?>"></script>
</head>
<body>
    <div class="page-container">
        <!-- ページヘッダー -->
        <div class="page-header">
            <div class="page-header-inner">
                <h1 class="page-title">シフト参加者一覧</h1>
                <div class="page-actions">
                    <a href="<?php echo \Fuel\Core\Uri::create('shifts'); ?>" class="btn btn-secondary">← シフト一覧へ</a>
                </div>
            </div>
        </div>

        <!-- シフト情報 -->
        <?php if (isset($shift) && $shift): ?>
        <div class="shift-info">
            <h2>シフト情報</h2>
            <div class="shift-details">
                <div class="shift-detail-item">
                    <span class="label">日付:</span>
                    <span class="value"><?php echo e($shift->shift_date); ?></span>
                </div>
                <div class="shift-detail-item">
                    <span class="label">時間:</span>
                    <span class="value"><?php echo e($shift->start_time . ' - ' . $shift->end_time); ?></span>
                </div>
                <div class="shift-detail-item">
                    <span class="label">募集人数:</span>
                    <span class="value"><?php echo e($shift->recruit_count); ?>名</span>
                </div>
                <?php if (isset($stats)): ?>
                <div class="shift-detail-item">
                    <span class="label">参加状況:</span>
                    <span class="value">
                        <?php echo e($stats['active_assignments']); ?>/<?php echo e($shift->recruit_count); ?>名
                        <?php if ($stats['active_assignments'] >= $shift->recruit_count): ?>
                            <span class="status-badge full">満員</span>
                        <?php else: ?>
                            <span class="status-badge available">募集中</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 参加者リスト -->
        <div class="participants-section">
            <h2>参加者一覧</h2>
            
            <?php if (empty($assignments)): ?>
                <div class="no-participants">
                    <p>まだ参加者がいません。</p>
                </div>
            <?php else: ?>
                <div class="participants-list">
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="participant-item" data-status="<?php echo e($assignment->status); ?>">
                            <div class="participant-avatar">
                                <div class="avatar-circle" style="background-color: <?php echo e($assignment->user ? $assignment->user->color : '#cccccc'); ?>">
                                    <?php echo e($assignment->user ? mb_substr($assignment->user->name, 0, 1) : '?'); ?>
                                </div>
                            </div>
                            
                            <div class="participant-info">
                                <div class="participant-name">
                                    <?php echo e($assignment->user ? $assignment->user->name : 'Unknown User'); ?>
                                </div>
                                
                                <?php if ($assignment->self_word): ?>
                                <div class="participant-comment">
                                    <strong>コメント:</strong> <?php echo e($assignment->self_word); ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="participant-meta">
                                    <span class="participant-status status-<?php echo e($assignment->status); ?>">
                                        <?php
                                        switch ($assignment->status) {
                                            case 'assigned':
                                                echo '参加予定';
                                                break;
                                            case 'confirmed':
                                                echo '確定';
                                                break;
                                            case 'cancelled':
                                                echo 'キャンセル';
                                                break;
                                            default:
                                                echo e($assignment->status);
                                        }
                                        ?>
                                    </span>
                                    <span class="participant-date">
                                        登録: <?php echo e(date('m/d H:i', strtotime($assignment->created_at))); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="participant-actions">
                                <?php if ($assignment->status !== 'cancelled'): ?>
                                    <button class="btn btn-sm btn-outline" onclick="confirmParticipant(<?php echo e($assignment->id); ?>)">
                                        確定
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="cancelParticipant(<?php echo e($assignment->id); ?>)">
                                        キャンセル
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 統計情報 -->
        <?php if (isset($stats)): ?>
        <div class="stats-section">
            <h3>統計情報</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label">総参加者数</span>
                    <span class="stat-value"><?php echo e($stats['total_assignments']); ?>名</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">参加予定</span>
                    <span class="stat-value"><?php echo e($stats['assigned_count']); ?>名</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">確定済み</span>
                    <span class="stat-value"><?php echo e($stats['confirmed_count']); ?>名</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">キャンセル</span>
                    <span class="stat-value"><?php echo e($stats['cancelled_count']); ?>名</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
        function confirmParticipant(assignmentId) {
            if (confirm('この参加者を確定しますか？')) {
                updateParticipantStatus(assignmentId, 'confirmed');
            }
        }
        
        function cancelParticipant(assignmentId) {
            if (confirm('この参加者をキャンセルしますか？')) {
                updateParticipantStatus(assignmentId, 'cancelled');
            }
        }
        
        function updateParticipantStatus(assignmentId, status) {
            $.ajax({
                url: '/api/shift_assignments/' + assignmentId + '/status',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({ status: status }),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('更新に失敗しました: ' + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    alert('更新に失敗しました: ' + error);
                }
            });
        }
    </script>
</body>
</html>
