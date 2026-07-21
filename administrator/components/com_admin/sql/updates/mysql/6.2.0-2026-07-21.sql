--
-- Add security column to updates table
--

ALTER TABLE `#__updates` ADD `security` TINYINT NULL DEFAULT NULL AFTER `extra_query`;
