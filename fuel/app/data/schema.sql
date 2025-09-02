-- ShiftBoard データベーススキーマ（更新版）
-- 作成日: 2025-09-02

-- ユーザーテーブル
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '表示名',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '作成時刻',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時刻',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザー管理テーブル';

-- シフトテーブル
CREATE TABLE IF NOT EXISTS `shifts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shift_date` date NOT NULL COMMENT '日付',
  `start_time` time NOT NULL COMMENT '開始時刻',
  `end_time` time NOT NULL COMMENT '終了時刻（start < end前提）',
  `slot_count` int unsigned NOT NULL DEFAULT '1' COMMENT '募集人数（上限チェック要）',
  `note` varchar(500) DEFAULT NULL COMMENT '備考（XSS対策して表示）',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '作成時刻',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時刻',
  PRIMARY KEY (`id`),
  KEY `idx_shift_date` (`shift_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='シフト枠管理テーブル';

-- ユーザーシフト割り当てテーブル
CREATE TABLE IF NOT EXISTS `user_shifts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'どのユーザーか',
  `shift_id` int NOT NULL COMMENT 'どのシフトの割合か',
  `role` enum('member','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member' COMMENT '権限',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '申込時刻',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時刻',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_shift` (`user_id`,`shift_id`),
  KEY `idx_role` (`role`),
  CONSTRAINT `fk_us_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_us_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーシフト割り当てテーブル';

-- 初期データの挿入
INSERT INTO `users` (`name`) VALUES
('管理者'),
('田中太郎'),
('佐藤花子'),
('鈴木一郎');

-- サンプルシフトデータ
INSERT INTO `shifts` (`shift_date`, `start_time`, `end_time`, `slot_count`, `note`) VALUES
('2025-09-03', '09:00:00', '17:00:00', 2, '平日シフト'),
('2025-09-04', '09:00:00', '17:00:00', 2, '平日シフト'),
('2025-09-05', '10:00:00', '18:00:00', 1, '遅番シフト'),
('2025-09-06', '09:00:00', '15:00:00', 3, '早番シフト');

-- サンプル割り当てデータ
INSERT INTO `user_shifts` (`user_id`, `shift_id`, `role`) VALUES
(1, 1, 'admin'), -- 管理者が9/3のシフトに割り当て
(2, 1, 'member'), -- 田中太郎が9/3のシフトに割り当て
(3, 2, 'member'), -- 佐藤花子が9/4のシフトに割り当て
(4, 2, 'member'), -- 鈴木一郎が9/4のシフトに割り当て
(2, 3, 'member'), -- 田中太郎が9/5のシフトに割り当て
(3, 4, 'member'); -- 佐藤花子が9/6のシフトに割り当て
