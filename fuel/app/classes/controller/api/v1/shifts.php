<?php
use Fuel\Core\Controller_Rest;
use Fuel\Core\Input;
use Fuel\Core\DB;

class Controller_Api_V1_Shifts extends Controller_Rest {
  protected $format = 'json';

  // --- 共通: JSON or x-www-form-urlencoded を配列で取得 ---
  private function payload(): array {
    $raw = @file_get_contents('php://input');
    if ($raw !== false && $raw !== '') {
      $json = json_decode($raw, true);
      if (is_array($json)) return $json;
    }
    // フォームPOSTのフォールバック
    return Input::post() ?: [];
  }

  public function get_index() {
    $start = Input::get('start') ?: date('Y-m-d');
    $end   = Input::get('end')   ?: date('Y-m-d');
    $rows = DB::query(
      'SELECT s.*,
              (SELECT COUNT(*) FROM shift_assignments sa
                 WHERE sa.shift_id = s.id AND sa.status <> "cancelled") AS assigned_count
         FROM shifts s
        WHERE s.shift_date BETWEEN :start AND :end
        ORDER BY s.shift_date, s.start_time'
    )->parameters(['start'=>$start,'end'=>$end])->execute()->as_array();
    return $this->response(['data'=>$rows], 200);
  }

  // 作成
  public function post_index() {
    try {
      $p = $this->payload();                             // ← 変更点
      foreach (['shift_date','start_time','end_time'] as $k)
        if (empty($p[$k])) return $this->response(['error'=>"$k required"], 400);
      if (strtotime($p['end_time']) <= strtotime($p['start_time']))
        return $this->response(['error'=>'end_time must be later than start_time'], 400);
      $slot = isset($p['slot_count']) ? max(1, (int)$p['slot_count']) : 1;

      list($id,) = DB::insert('shifts')->set([
        'shift_date'=>$p['shift_date'],
        'start_time'=>$p['start_time'],
        'end_time'  =>$p['end_time'],
        'note'      =>isset($p['note'])?mb_substr($p['note'],0,500):null,
        'slot_count'=>$slot,
      ])->execute();

      return $this->response(['data'=>['id'=>$id]], 201);
    } catch (\Throwable $e) {
      return $this->response(['error'=>'server_error','message'=>$e->getMessage()], 500);
    }
  }

  // 応募
  public function post_apply($id)
  {
    $p = $this->payload();
    $member_id = $p['member_id'] ?? Input::headers('X-Member-Id');
    if (!$member_id) return $this->response(['error'=>'member_id required'], 400);
  
    // 1) 存在チェック
    $shift = DB::query('SELECT slot_count FROM shifts WHERE id=:id')
              ->parameters(['id'=>$id])->execute()->current();
    if (!$shift) return $this->response(['error'=>'shift not found'], 404);
  
    $member = DB::query('SELECT id FROM members WHERE id=:mid AND is_active=1')
               ->parameters(['mid'=>$member_id])->execute()->current();
    if (!$member) return $this->response(['error'=>'member not found'], 404);
  
    // 2) 枠上限
    $assigned = DB::query('SELECT COUNT(*) AS c FROM shift_assignments
                            WHERE shift_id=:id AND status<>"cancelled"')
                 ->parameters(['id'=>$id])->execute()->current()['c'];
    if ((int)$assigned >= (int)$shift['slot_count'])
      return $this->response(['error'=>'slot full'], 409);
  
    // 3) 既存行の状態で分岐（キャンセル済なら復活）
    $existing = DB::query('SELECT status FROM shift_assignments
                            WHERE shift_id=:id AND member_id=:mid')
                 ->parameters(['id'=>$id,'mid'=>$member_id])->execute()->current();
    if ($existing) {
      if ($existing['status'] === 'cancelled') {
        DB::query('UPDATE shift_assignments SET status="applied"
                    WHERE shift_id=:id AND member_id=:mid')
          ->parameters(['id'=>$id,'mid'=>$member_id])->execute();
        return $this->response(['ok'=>true], 201);
      }
      return $this->response(['error'=>'already applied'], 409);
    }
  
    // 4) 新規作成
    DB::insert('shift_assignments')->set([
      'shift_id'=>$id,'member_id'=>$member_id,'status'=>'applied'
    ])->execute();
  
    return $this->response(['ok'=>true], 201);
  }

  // 取消
  public function post_cancel($id)
  {
    $p = $this->payload();
    $member_id = $p['member_id'] ?? Input::headers('X-Member-Id');
    if (!$member_id) return $this->response(['error'=>'member_id required'], 400);
  
    $existing = DB::query('SELECT status FROM shift_assignments
                            WHERE shift_id=:id AND member_id=:mid')
                 ->parameters(['id'=>$id,'mid'=>$member_id])->execute()->current();
    if (!$existing) return $this->response(['error'=>'not applied'], 404);
  
    DB::query('UPDATE shift_assignments SET status="cancelled"
                WHERE shift_id=:id AND member_id=:mid')
      ->parameters(['id'=>$id,'mid'=>$member_id])->execute();
  
    return $this->response(['ok'=>true], 200);
  }