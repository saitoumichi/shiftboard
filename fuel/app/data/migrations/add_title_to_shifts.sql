-- シフトテーブルにtitleフィールドを追加するマイグレーション

-- titleフィールドを追加
ALTER TABLE `shifts` ADD COLUMN `title` varchar(200) DEFAULT NULL AFTER `id`;

-- 既存のデータにデフォルトのタイトルを設定
UPDATE `shifts` SET `title` = CONCAT(DATE_FORMAT(`shift_date`, '%m月%d日'), 'のシフト') WHERE `title` IS NULL;

-- titleフィールドをNOT NULLに変更（デフォルト値を設定後）
ALTER TABLE `shifts` MODIFY COLUMN `title` varchar(200) NOT NULL;

-- インデックスを追加（検索性能向上のため）
CREATE INDEX `idx_shifts_title` ON `shifts` (`title`);
