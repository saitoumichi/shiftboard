-- シフトボードアプリケーションのデータベーススキーマ
-- このファイルは本番環境のデータベーススキーマを定義します

-- users
CREATE TABLE users (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(100)    NOT NULL,
  color      CHAR(7)         NULL,
  created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL           DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- shifts
CREATE TABLE shifts (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  created_by    BIGINT UNSIGNED NOT NULL,              -- シフト作った人
  shift_date    DATE            NOT NULL,
  start_time    TIME            NOT NULL,
  end_time      TIME            NOT NULL,
  recruit_count INT  UNSIGNED   NOT NULL DEFAULT 1,    -- 募集人数
  free_text     VARCHAR(500)    NULL,                   -- 自由記述（やる気を書くとか）
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL           DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_shifts_date       (shift_date),
  KEY idx_shifts_date_time  (shift_date, start_time),
  KEY idx_shifts_created_by (created_by),
  CONSTRAINT fk_shifts_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_time_order CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- shift_assignments
CREATE TABLE shift_assignments (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  shift_id   BIGINT UNSIGNED NOT NULL,
  user_id    BIGINT UNSIGNED NOT NULL,
  self_word  VARCHAR(500)    NULL,
  status     ENUM('assigned','confirmed','cancelled') NOT NULL DEFAULT 'assigned',
  created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL           DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_shift_user (shift_id, user_id),  -- ★ 重複割り当て防止
  KEY idx_assign_user    (user_id),
  KEY idx_assign_status  (status),
  CONSTRAINT fk_assign_shift
    FOREIGN KEY (shift_id) REFERENCES shifts(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_assign_user
    FOREIGN KEY (user_id)  REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
