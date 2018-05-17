--
-- Add access column for access levels #__users
--

ALTER TABLE `#__users` ADD COLUMN `access` int(10) unsigned NOT NULL DEFAULT 1;
