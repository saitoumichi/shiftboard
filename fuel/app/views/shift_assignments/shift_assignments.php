<?php use Fuel\Core\Uri; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>自分のシフト</title>
    <link rel="stylesheet" href="<?= Uri::create('css/common.css') ?>">
    </head>
<body>
    <h1>自分のシフト</h1>

    <?php if (empty($assignments)): ?>
        <p>参加中のシフトはありません。</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>時間</th>
                    <th>メモ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $a): ?>
                <?php $s = $a->shift ?? null; ?>
                <tr>
                    <td><?php echo $s ? e($s->shift_date) : '-'; ?></td>
                    <td><?php echo $s ? e($s->start_time.' - '.$s->end_time) : '-'; ?></td>
                    <td><?php echo e($a->self_word ?: ($s->free_text ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="<?php echo \Fuel\Core\Uri::create('shifts'); ?>">← シフト一覧へ</a></p>
</body>
</html>