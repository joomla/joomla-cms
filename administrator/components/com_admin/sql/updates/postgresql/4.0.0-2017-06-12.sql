ALTER TABLE `#__update_sites` ADD COLUMN `checked_out` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `#__update_sites` ADD COLUMN `checked_out_time` timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL;
ALTER TABLE `#__update_sites` ADD COLUMN `ordering` int(10) unsigned NOT NULL DEFAULT 0;
