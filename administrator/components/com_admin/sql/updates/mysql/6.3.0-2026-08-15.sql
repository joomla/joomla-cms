--
-- Add outdated column to associations table
--

ALTER TABLE `#__associations` ADD COLUMN `outdated` tinyint NOT NULL DEFAULT 0 AFTER `key` /** CAN FAIL **/;
