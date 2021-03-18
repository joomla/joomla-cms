ALTER TABLE `#__categories` MODIFY `created_time` datetime NOT NULL;
ALTER TABLE `#__categories` MODIFY `modified_time` datetime NOT NULL;

ALTER TABLE `#__categories` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__categories` SET `modified_time` = `created_time`, `modified_user_id` = `created_user_id` WHERE `modified_time` = '0000-00-00 00:00:00';

UPDATE `#__categories` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias` IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
 WHERE `core_type_alias`IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND `core_checked_out_time` = '0000-00-00 00:00:00';
