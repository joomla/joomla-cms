ALTER TABLE `#__user_notes` MODIFY `created_time` datetime NOT NULL;
ALTER TABLE `#__user_notes` MODIFY `modified_time` datetime NOT NULL;

ALTER TABLE `#__user_notes` MODIFY `review_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__user_notes` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__user_notes` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__user_notes` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__user_notes` SET `modified_time` = `created_time`, `modified_user_id` = `created_user_id` WHERE `modified_time` = '0000-00-00 00:00:00';

UPDATE `#__user_notes` SET `review_time` = NULL WHERE `review_time` = '0000-00-00 00:00:00';
UPDATE `#__user_notes` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__user_notes` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__user_notes` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias` = 'com_users.note'
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_publish_up` = NULL
 WHERE `core_type_alias` = 'com_users.note'
   AND `core_publish_up` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_publish_down` = NULL
 WHERE `core_type_alias` = 'com_users.note'
   AND `core_publish_down` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
 WHERE `core_type_alias` = 'com_users.note'
   AND `core_checked_out_time` = '0000-00-00 00:00:00';
