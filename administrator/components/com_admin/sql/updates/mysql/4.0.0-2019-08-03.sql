ALTER TABLE `#__update_sites` ADD COLUMN `checked_out` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__update_sites` ADD COLUMN `checked_out_time` datetime NULL DEFAULT NULL;
