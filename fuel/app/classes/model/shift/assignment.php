<?php

use Fuel\Core\DB;

class Model_Shift_Assignment extends \Orm\Model
{
    protected static $_table_name = 'shift_assignments';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id',
        'shift_id',    // 参加するシフト(shifts.idを参照)
        'user_id',     // 参加するユーザー(users.idを参照)
        'status',      // 参加状況: 'assigned', 'confirmed', 'cancelled'
        'self_word',   // 参加する際の一言
        'created_at',
        'updated_at',
    );

    protected static $_observers = array(
      'Orm\\Observer_CreatedAt' => array(
          'events'           => array('before_insert'),
          'mysql_timestamp'  => true,  // 'Y-m-d H:i:s' を自動セット
      ),
      'Orm\\Observer_UpdatedAt' => array(
          'events'           => array('before_update'),
          'mysql_timestamp'  => true,
      ),
  );

  // デフォルト値（status は常に 'assigned' で設定）
  protected static $_defaults = array(
      'status' => 'assigned',
  );

    // シフト割り当て N : 1 ユーザー
    protected static $_belongs_to = [
        'user' => [
            'key_from'       => 'user_id',
            'model_to'       => 'Model_User',
            'key_to'         => 'id',
            'cascade_save'   => false,
            'cascade_delete' => false,
        ],
        // シフト割り当て N : 1 シフト
        'shift' => [
            'key_from'       => 'shift_id',
            'model_to'       => 'Model_Shift',
            'key_to'         => 'id',
            'cascade_save'   => false,
            'cascade_delete' => false,
        ],
    ];

    /**
     * アクティブな割り当てかどうか（キャンセル済みでない）
     */
    public function is_active(): bool
    {
        return $this->status !== 'cancelled';
    }

    /**
     * 特定のシフトとユーザーの重複割り当てをチェック
     */
    public static function exists_for_shift_and_user($shift_id, $user_id, $exclude_cancelled = true): bool
    {
        $q = static::query()
        ->where('shift_id', $shift_id)
        ->where('user_id', $user_id);

    if ($exclude_cancelled) {
        $q->where('status', '!=', 'cancelled');
    }
    return $q->get_one() !== null;
    }

    /**
     * 特定のシフトのアクティブな割り当て数を取得
     */
    public static function count_active_for_shift($shift_id): int
    {
        $result = DB::select(DB::expr('COUNT(*) as count'))
            ->from('shift_assignments')
            ->where('shift_id', (int)$shift_id)
            ->where('status', '!=', 'cancelled')
            ->execute();
        
        return (int)$result->get('count', 0);
    }
}