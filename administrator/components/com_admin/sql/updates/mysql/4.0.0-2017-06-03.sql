ALTER TABLE `#__updates` ADD COLUMN `changelogurl` text AFTER `infourl`;
ALTER TABLE `#__update_sites` ADD COLUMN `checked_out` int(10) unsigned;
ALTER TABLE `#__update_sites` ADD COLUMN `checked_out_time` datetime;
