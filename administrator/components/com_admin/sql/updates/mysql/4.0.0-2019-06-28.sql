ALTER TABLE `#__banners` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__banners` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
