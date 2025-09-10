# データベーススキーマ変更点

## 提供されたスキーマとの整合性確認

### 1. `members`テーブル
```sql
CREATE TABLE `members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` enum('member','admin') NOT NULL DEFAULT 'member',
  `color` char(7) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**変更点**: なし（既存のコードと完全に一致）

### 2. `shifts`テーブル
```sql
CREATE TABLE `shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `note` varchar(500) DEFAULT NULL,
  `slot_count` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**変更点**: 
- `available_slots`カラムを削除（動的計算のため）
- 既存のコードは既に`available_slots`を保存していない

### 3. `shift_assignments`テーブル
```sql
CREATE TABLE `shift_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shift_id` bigint unsigned NOT NULL,
  `member_id` bigint unsigned NOT NULL,
  `status` enum('applied','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_shift_member` (`shift_id`,`member_id`),
  CONSTRAINT `shift_assignments_ibfk_1` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_assignments_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**変更点**:
- `status`のデフォルト値が`'confirmed'`に設定されている
- コードで明示的に`'confirmed'`を指定する必要がなくなった

## コードの修正内容

### 1. `action_join`メソッドの修正
```php
// 修正前
INSERT INTO shift_assignments (member_id, shift_id, status, created_at) 
VALUES (?, ?, 'confirmed', NOW())

// 修正後（デフォルト値を使用）
INSERT INTO shift_assignments (member_id, shift_id, created_at) 
VALUES (?, ?, NOW())
```

### 2. ステータス値の確認
- `'applied'`: 申請中
- `'confirmed'`: 確定（デフォルト）
- `'cancelled'`: キャンセル

### 3. 既存のクエリの確認
すべてのクエリで`status != 'cancelled'`を条件にしているため、`'applied'`と`'confirmed'`の両方が有効な参加として扱われます。

## マイグレーション手順

1. **既存データベースの場合**:
   ```sql
   -- fuel/app/data/migrations/remove_available_slots.sql を実行
   ```

2. **新規データベースの場合**:
   ```sql
   -- fuel/app/data/schema.sql を実行
   ```

## パフォーマンス向上のためのインデックス

```sql
CREATE INDEX `idx_shifts_date` ON `shifts` (`shift_date`);
CREATE INDEX `idx_shifts_date_time` ON `shifts` (`shift_date`, `start_time`);
CREATE INDEX `idx_shift_assignments_member` ON `shift_assignments` (`member_id`);
CREATE INDEX `idx_shift_assignments_status` ON `shift_assignments` (`status`);
```

## サンプルデータ

開発環境用のサンプルデータも`schema.sql`に含まれています：
- 管理者1名
- メンバー3名
- サンプルシフト3件
- サンプル参加データ5件
