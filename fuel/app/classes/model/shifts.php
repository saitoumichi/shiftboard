<?php

class Model_Shifts
{
    /** 一覧（未来のみ + 集計付き + ページング） */
    public static function list_with_counts(array $opts = []): array
    {
        $page = max(1, (int)($opts['page'] ?? 1));
        $per  = min(100, max(1, (int)($opts['per'] ?? 20)));
        $off  = ($page - 1) * $per;
        $only_open = !empty($opts['only_open']);

        // 総数（未来のみ）
        $total = \DB::query("SELECT COUNT(*) AS c FROM shifts WHERE shift_date >= CURDATE()")
            ->execute()->current();
        $total = (int)($total['c'] ?? 0);

        // 本体
        $sql = "
          SELECT
            s.id, s.created_by, s.shift_date, s.start_time, s.end_time,
            s.recruit_count, s.free_text,
            COALESCE(SUM(CASE WHEN sa.status <> 'cancelled' THEN 1 ELSE 0 END), 0) AS joined_count
          FROM shifts s
          LEFT JOIN shift_assignments sa ON sa.shift_id = s.id
          WHERE s.shift_date >= CURDATE()
          GROUP BY s.id, s.created_by, s.shift_date, s.start_time, s.end_time, s.recruit_count, s.free_text
        ";
        if ($only_open) {
            $sql .= " HAVING COALESCE(SUM(CASE WHEN sa.status <> 'cancelled' THEN 1 ELSE 0 END), 0) < s.recruit_count ";
        }
        $sql .= " ORDER BY s.shift_date, s.start_time LIMIT :per OFFSET :off ";

        $rows = \DB::query($sql)
            ->parameters(['per' => $per, 'off' => $off])
            ->execute()->as_array();

        // 整形
        $items = array_map(function($s){
            $joined = (int)$s['joined_count'];
            $cap    = (int)$s['recruit_count'];
            return [
                'id'            => (int)$s['id'],
                'created_by'    => (int)$s['created_by'],
                'shift_date'    => (string)$s['shift_date'],
                'start_time'    => (string)$s['start_time'],
                'end_time'      => (string)$s['end_time'],
                'recruit_count' => $cap,
                'joined_count'  => $joined,
                'remaining'     => max($cap - $joined, 0),
                'free_text'     => $s['free_text'] ?? null,
            ];
        }, $rows);

        return ['items' => $items, 'page' => $page, 'per_page' => $per, 'total' => $total];
    }

    /** 詳細（1件＋集計） */
    public static function find_one(int $id): ?array
    {
        $row = \DB::query("
          SELECT
            s.id, s.created_by, s.shift_date, s.start_time, s.end_time,
            s.recruit_count, s.free_text,
            COALESCE(SUM(CASE WHEN sa.status <> 'cancelled' THEN 1 ELSE 0 END), 0) AS joined_count
          FROM shifts s
          LEFT JOIN shift_assignments sa ON sa.shift_id = s.id
          WHERE s.id = :id
          GROUP BY s.id, s.created_by, s.shift_date, s.start_time, s.end_time, s.recruit_count, s.free_text
        ")->parameters(['id' => $id])->execute()->current();

        if (!$row) return null;

        $joined = (int)$row['joined_count'];
        $cap    = (int)$row['recruit_count'];
        return [
            'id'            => (int)$row['id'],
            'created_by'    => (int)$row['created_by'],
            'shift_date'    => (string)$row['shift_date'],
            'start_time'    => (string)$row['start_time'],
            'end_time'      => (string)$row['end_time'],
            'recruit_count' => $cap,
            'joined_count'  => $joined,
            'remaining'     => max($cap - $joined, 0),
            'free_text'     => $row['free_text'] ?? null,
        ];
    }

    /** バリデーション（最小） */
    public static function validate(array $data): array
    {
        $errs = [];
        if (empty($data['created_by']))   $errs[] = 'created_by is required';
        if (empty($data['shift_date']))   $errs[] = 'shift_date is required';
        if (empty($data['start_time']))   $errs[] = 'start_time is required';
        if (empty($data['end_time']))     $errs[] = 'end_time is required';
        if (!isset($data['recruit_count']) || (int)$data['recruit_count'] < 1) $errs[] = 'recruit_count >= 1';
        return $errs;
    }

    /** 作成 */
    public static function create(array $data): int
    {
        $errs = self::validate($data);
        if ($errs) {
            throw new \InvalidArgumentException(implode(', ', $errs));
        }
        list($id,) = \DB::query("
          INSERT INTO shifts (created_by, shift_date, start_time, end_time, recruit_count, free_text, created_at)
          VALUES (:created_by, :shift_date, :start_time, :end_time, :recruit_count, :free_text, NOW())
        ")->parameters([
            'created_by'    => (int)$data['created_by'],
            'shift_date'    => $data['shift_date'],
            'start_time'    => $data['start_time'],
            'end_time'      => $data['end_time'],
            'recruit_count' => (int)$data['recruit_count'],
            'free_text'     => $data['free_text'] ?? null,
        ])->execute();
        return (int)$id;
    }

    /** 更新 */
    public static function update(int $id, array $data): bool
    {
        // 部分更新OK・必要なものだけ反映
        $sets = []; $params = ['id' => $id];
        foreach (['shift_date','start_time','end_time','recruit_count','free_text'] as $k) {
            if (array_key_exists($k, $data)) {
                $sets[] = "$k = :$k";
                $params[$k] = $k === 'recruit_count' ? (int)$data[$k] : $data[$k];
            }
        }
        if (!$sets) return false;

        $sql = "UPDATE shifts SET ".implode(',', $sets).", updated_at = NOW() WHERE id = :id";
        $res = \DB::query($sql)->parameters($params)->execute();
        return (int)$res > 0;
    }

    /** 削除（参加行もON DELETE CASCADE前提） */
    public static function delete(int $id): bool
    {
        $res = \DB::query("DELETE FROM shifts WHERE id = :id")->parameters(['id'=>$id])->execute();
        return (int)$res > 0;
    }
}