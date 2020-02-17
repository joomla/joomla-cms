ALTER TABLE `#__modules` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__modules` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__modules` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

-- Use 0 instead of '0000-00-00 00:00:00' if you get 'Invalid default value for ...'
UPDATE `#__modules` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__modules` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__modules` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
