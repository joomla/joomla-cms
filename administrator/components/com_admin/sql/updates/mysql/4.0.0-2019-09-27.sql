ALTER TABLE `#__tags` MODIFY `created_time` datetime NOT NULL;
ALTER TABLE `#__tags` MODIFY `modified_time` datetime NOT NULL;

ALTER TABLE `#__tags` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__tags` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__tags` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__tags` SET `modified_time` = `created_time`, `modified_user_id` = `created_user_id` WHERE `modified_time` = '0000-00-00 00:00:00';

UPDATE `#__tags` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__tags` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__tags` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias` = 'com_tags.tag'
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_publish_up` = NULL
 WHERE `core_type_alias` = 'com_tags.tag'
   AND `core_publish_up` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_publish_down` = NULL
 WHERE `core_type_alias` = 'com_tags.tag'
   AND `core_publish_down` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
 WHERE `core_type_alias` = 'com_tags.tag'
   AND `core_checked_out_time` = '0000-00-00 00:00:00';
