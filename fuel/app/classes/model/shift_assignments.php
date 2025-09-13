<?php
class Model_Shifts_Participation extends Orm\Model
{
    protected static $_table_name  = 'shifts_participations';
    protected static $_primary_key = ['id'];

    protected static $_properties  = [
        'id',
        'shift_id',
        'user_id',
        'self_word',
        'status',
        'created_at',
        'updated_at',
    ];

    protected static $_belongs_to = [
        'shift' => [
            'model_to' => 'Model_Shift',
            'key_from' => 'shift_id',
            'key_to'   => 'id',
        ],
        'user' => [
            'model_to' => 'Model_User',
            'key_from' => 'user_id',
            'key_to'   => 'id',
        ],
    ];

    protected static $_observers = [
        'Orm\\Observer_Typing'   => ['events' => ['before_save']],
        'Orm\\Observer_CreatedAt'=> ['events' => ['before_insert'], 'mysql_timestamp' => true],
        'Orm\\Observer_UpdatedAt'=> ['events' => ['before_save'],   'mysql_timestamp' => true],
    ];
    // `Model_Shifts` クラス内に以下のメソッドを追加・修正
    
    /**
     * シフト一覧と参加者数を取得
     * ORMを使用してJOIN操作を行う
     */
    public static function get_all_with_assignments()
    {
        $rows = static::query()
            ->select('t0.*', \DB::expr("COUNT(CASE WHEN t1.status != 'cancelled' THEN t1.user_id END) AS joined_count"))
            ->related('assignments', [
                'where' => [
                    ['assignments.status', '!=', 'cancelled']
                ],
                'join_type' => 'left'
            ])
            ->group_by('t0.id')
            ->order_by('shift_date', 'asc')
            ->order_by('start_time', 'asc')
            ->get();
            
        return array_map(function($row) {
            $data = $row->to_array();
            $data['joined_count'] = $data['joined_count'] ?? 0;
            return $data;
        }, $rows);
    }
    
    /**
     * シフト詳細と参加者情報を取得
     */
    public static function find_by_id_with_assignments($id)
    {
        $shift = static::find($id, [
            'related' => ['assignments']
        ]);
        
        if ($shift) {
            $shift_data = $shift->to_array();
            $assigned_users = [];
            foreach ($shift->assignments as $assignment) {
                if ($assignment->status !== 'cancelled') {
                    // ここでユーザー情報を取得するために `Model_Shift_Assignments` のリレーションを利用する
                    $user = \Model_Users::find($assignment->user_id);
                    $assigned_users[] = [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'status' => $assignment->status,
                        'self_word' => $assignment->self_word
                    ];
                }
            }
            $shift_data['assigned_users'] = $assigned_users;
            return $shift_data;
        }
        return null;
    }
}