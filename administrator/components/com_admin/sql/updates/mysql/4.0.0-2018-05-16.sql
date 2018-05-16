--
-- Add access column for access levels #__content
--

ALTER TABLE `#__content` ADD COLUMN `access` int(10) unsigned NOT NULL DEFAULT 0;
