ALTER TABLE `#__ucm_content` MODIFY `core_created_time` datetime NOT NULL;
ALTER TABLE `#__ucm_content` MODIFY `core_modified_time` datetime NOT NULL;

ALTER TABLE `#__ucm_content` MODIFY `core_publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__ucm_content` MODIFY `core_publish_down` datetime NULL DEFAULT NULL;

-- Only on MySQL: Update empty strings to null date before converting the column from varchar to datetime
UPDATE `#__ucm_content` SET `core_checked_out_time` = '0000-00-00 00:00:00' WHERE `core_checked_out_time` = '';

ALTER TABLE `#__ucm_content` MODIFY `core_checked_out_time` datetime NULL DEFAULT NULL;
