--
-- Add security column to updates table
--

ALTER TABLE `#__updates` ADD COLUMN `security` TINYINT NULL DEFAULT NULL AFTER `extra_query` /** CAN FAIL **/;
