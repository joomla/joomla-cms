-- From 4.0.0-2018-08-01.sql
ALTER TABLE `#__ucm_content` MODIFY `core_created_time` datetime NOT NULL;
ALTER TABLE `#__ucm_content` MODIFY `core_modified_time` datetime NOT NULL;

ALTER TABLE `#__ucm_content` MODIFY `core_publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__ucm_content` MODIFY `core_publish_down` datetime NULL DEFAULT NULL;

-- Only on MySQL: Update empty strings to null date before converting the column from varchar to datetime
-- The following statement was modified for 4.1.1 by adding a check for NULL and a type cast to CHAR.
-- See https://github.com/joomla/joomla-cms/pull/37156
UPDATE `#__ucm_content` SET `core_checked_out_time` = '0000-00-00 00:00:00' WHERE `core_checked_out_time` IS NOT NULL AND CAST(`core_checked_out_time` AS char) = '';

ALTER TABLE `#__ucm_content` MODIFY `core_checked_out_time` datetime NULL DEFAULT NULL;

-- From 4.0.0-2018-08-29.sql
ALTER TABLE `#__modules` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__modules` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__modules` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

-- Use 0 instead of '0000-00-00 00:00:00' if you get 'Invalid default value for ...'
UPDATE `#__modules` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__modules` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__modules` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
