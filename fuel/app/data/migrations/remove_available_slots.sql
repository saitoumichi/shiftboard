-- available_slotsカラムを削除するマイグレーション
-- このカラムは動的に計算されるため、データベースに保存する必要がありません

-- 理由:
-- 1. available_slotsは参加者の増減によって変動する
-- 2. データベースに保存するとデータ不整合の原因となる
-- 3. Controller_Api_Common::formatShiftData()で動的に計算される

-- 既存のテーブルがある場合のみ実行
-- shiftsテーブルからavailable_slotsカラムを削除（存在する場合のみ）
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'shifts' 
    AND COLUMN_NAME = 'available_slots'
    AND TABLE_SCHEMA = DATABASE()
);

SET @sql = IF(@column_exists > 0, 
    'ALTER TABLE shifts DROP COLUMN available_slots', 
    'SELECT "available_slotsカラムは存在しません" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 確認用クエリ（マイグレーション実行後に実行）
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'shifts' AND COLUMN_NAME = 'available_slots';

-- 注意: このマイグレーションを実行する前に、データベースのバックアップを取ってください
