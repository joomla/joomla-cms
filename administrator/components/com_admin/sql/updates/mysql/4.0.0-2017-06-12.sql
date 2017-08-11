ALTER TABLE `#__update_sites` ADD COLUMN `checked_out` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__update_sites` ADD COLUMN `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__update_sites` ADD COLUMN `ordering` int(10) unsigned NOT NULL DEFAULT 0;
