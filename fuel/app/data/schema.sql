-- メンバーテーブル
CREATE TABLE members (
id bigint unsigned NOT NULL AUTO_INCREMENT,
name varchar(100) NOT NULL COMMENT '表示名（utf8mb4）',
role enum('member','admin') NOT NULL DEFAULT 'member' COMMENT '権限（最小2種）',
color char(7) DEFAULT NULL COMMENT '表示色 #RRGGBB',
is_active tinyint(1) NOT NULL DEFAULT 1 COMMENT '0:無効 / 1:有効',
created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成時刻',
updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時刻',
PRIMARY KEY (id),
KEY idx_is_active (is_active),
KEY idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='メンバー管理テーブル';

-- シフトテーブル
CREATE TABLE shifts (
id bigint unsigned NOT NULL AUTO_INCREMENT,
shift_date date NOT NULL COMMENT '日付（索引あり）',
start_time time NOT NULL COMMENT '開始時刻',
end_time time NOT NULL COMMENT '終了時刻（start < end前提）',
note varchar(500) DEFAULT NULL COMMENT '備考（XSS対策して表示）',
slot_count int unsigned NOT NULL DEFAULT 1 COMMENT '募集人数（上限チェック要）',
created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成時刻',
updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時刻',
PRIMARY KEY (id),
KEY idx_shift_date (shift_date),
KEY idx_start_time (start_time),
KEY idx_end_time (end_time),
CONSTRAINT chk_time_order CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='シフト枠管理テーブル';

-- シフト割り当てテーブル
CREATE TABLE shift_assignments (
id bigint unsigned NOT NULL AUTO_INCREMENT,
shift_id bigint unsigned NOT NULL COMMENT 'FK→shifts.id（CASCADE）',
member_id bigint unsigned NOT NULL COMMENT 'FK→members.id（CASCADE）',
status enum('applied','confirmed','cancelled') NOT NULL DEFAULT 'confirmed' COMMENT '状態',
created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '申込時刻',
updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時刻',
PRIMARY KEY (id),
UNIQUE KEY unique_shift_member (shift_id, member_id) COMMENT '重複登録防止',
KEY idx_shift_id (shift_id),
KEY idx_member_id (member_id),
KEY idx_status (status),
CONSTRAINT fk_shift_assignments_shift FOREIGN KEY (shift_id) REFERENCES shifts (id) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT fk_shift_assignments_member FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='シフト割り当てテーブル';

-- 初期データ
INSERT INTO members (name, role, color, is_active) VALUES
('管理者', 'admin', '#FF6B6B', 1),
('田中太郎', 'member', '#4ECDC4', 1),
('佐藤花子', 'member', '#45B7D1', 1),
('鈴木一郎', 'member', '#96CEB4', 1),
('高橋美咲', 'member', '#FFEAA7', 1);

INSERT INTO shifts (shift_date, start_time, end_time, note, slot_count) VALUES
('2025-09-03', '09:00:00', '17:00:00', '平日シフト', 2),
('2025-09-04', '09:00:00', '17:00:00', '平日シフト', 2),
('2025-09-05', '10:00:00', '18:00:00', '遅番シフト', 1),
('2025-09-06', '09:00:00', '15:00:00', '早番シフト', 3);

INSERT INTO shift_assignments (shift_id, member_id, status) VALUES
(1, 1, 'confirmed'),
(1, 2, 'confirmed'),
(2, 3, 'confirmed'),
(2, 4, 'confirmed'),
(3, 2, 'confirmed'),
(4, 3, 'confirmed');