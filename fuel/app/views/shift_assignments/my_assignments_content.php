<div class="container">
    <h1>マイシフト</h1>
    
    <?php if (empty($assignments)): ?>
        <div class="alert alert-info">
            <p>参加中のシフトはありません。</p>
        </div>
    <?php else: ?>
        <div class="shifts-list">
            <?php foreach ($assignments as $assignment): ?>
                <div class="shift-item">
                    <h3>
                        <a href="/shifts/<?php echo $assignment->shift->id; ?>">
                            <?php echo $assignment->shift->shift_date; ?> 
                            <?php echo substr($assignment->shift->start_time, 0, 5); ?> - 
                            <?php echo substr($assignment->shift->end_time, 0, 5); ?>
                        </a>
                    </h3>
                    <p>ステータス: <?php echo $assignment->status; ?></p>
                    <?php if ($assignment->self_word): ?>
                        <div class="comment-section">
                            <strong>コメント:</strong>
                            <div class="comment-text"><?php echo htmlspecialchars($assignment->self_word); ?></div>
                        </div>
                    <?php endif; ?>
                    <p>募集人数: <?php echo $assignment->shift->recruit_count; ?>人</p>
                    <?php if ($assignment->shift->free_text): ?>
                        <p>備考: <?php echo htmlspecialchars($assignment->shift->free_text); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="actions">
        <a href="/shifts" class="btn btn-primary">シフト一覧に戻る</a>
    </div>
</div>

<style>
.shift-item {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 15px;
    background-color: #f9f9f9;
}

.shift-item h3 {
    margin-top: 0;
    color: #333;
}

.shift-item h3 a {
    text-decoration: none;
    color: #007bff;
}

.shift-item h3 a:hover {
    text-decoration: underline;
}

.actions {
    margin-top: 20px;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}

.btn:hover {
    background-color: #0056b3;
    color: white;
    text-decoration: none;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-info {
    color: #31708f;
    background-color: #d9edf7;
    border-color: #bce8f1;
}

.comment-section {
    margin: 10px 0;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #007bff;
}

.comment-section strong {
    color: #007bff;
    font-weight: 600;
}

.comment-text {
    margin-top: 5px;
    color: #495057;
    font-style: italic;
}
</style>
