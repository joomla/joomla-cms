ALTER TABLE `#__redirect_links` ADD COLUMN `hits` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `comment`;
ALTER TABLE `#__users` ADD COLUMN `lastResetTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date of last password reset';
ALTER TABLE `#__users` ADD COLUMN `resetCount` int(11) NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime';