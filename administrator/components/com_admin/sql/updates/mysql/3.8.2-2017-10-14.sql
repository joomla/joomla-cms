--
-- Add index for alias check #__content
--

ALTER TABLE `#__content` ADD INDEX `idx_alias` (`alias`(191));
