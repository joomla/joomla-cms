ALTER TABLE `#__menu` ADD COLUMN `publish_up` datetime;
ALTER TABLE `#__menu` ADD COLUMN `publish_down` datetime; 

ALTER TABLE `#__menu` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__menu` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
