-- From 4.0.0-2019-09-23.sql
ALTER TABLE `#__redirect_links` MODIFY `created_date` datetime NOT NULL;
ALTER TABLE `#__redirect_links` MODIFY `modified_date` datetime NOT NULL;

UPDATE `#__redirect_links` SET `created_date` = '1980-01-01 00:00:00' WHERE `created_date` = '0000-00-00 00:00:00';
UPDATE `#__redirect_links` SET `modified_date` = `created_date` WHERE `modified_date` = '0000-00-00 00:00:00';

-- From 4.0.0-2019-09-24.sql
ALTER TABLE `#__action_logs` MODIFY `log_date` datetime NOT NULL;

-- From 4.0.0-2019-09-25.sql
ALTER TABLE `#__fields` MODIFY `created_time` datetime NOT NULL;
ALTER TABLE `#__fields` MODIFY `modified_time` datetime NOT NULL;
ALTER TABLE `#__fields` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

ALTER TABLE `#__fields_groups` MODIFY `created` datetime NOT NULL;
ALTER TABLE `#__fields_groups` MODIFY `modified` datetime NOT NULL;
ALTER TABLE `#__fields_groups` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__fields` SET `modified_time` = `created_time`, `modified_by` = `created_user_id` WHERE `modified_time` = '0000-00-00 00:00:00';
UPDATE `#__fields` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__fields_groups` SET `modified` = `created`, `modified_by` = `created_by` WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__fields_groups` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

-- From 4.0.0-2019-09-26.sql
ALTER TABLE `#__privacy_requests` MODIFY `requested_at` datetime NOT NULL;
ALTER TABLE `#__privacy_requests` MODIFY `confirm_token_created_at` datetime NULL DEFAULT NULL;

ALTER TABLE `#__privacy_consents` MODIFY `created` datetime NOT NULL;

UPDATE `#__privacy_requests` SET `confirm_token_created_at` = NULL WHERE `confirm_token_created_at` = '0000-00-00 00:00:00';

-- From 4.0.0-2019-09-27.sql
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

-- From 4.0.0-2019-09-28.sql
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

-- From 4.0.0-2019-09-29.sql
ALTER TABLE `#__categories` MODIFY `created_time` datetime NOT NULL;
ALTER TABLE `#__categories` MODIFY `modified_time` datetime NOT NULL;

ALTER TABLE `#__categories` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__categories` SET `created_time` = '1980-01-01 00:00:00' WHERE `created_time` = '0000-00-00 00:00:00';
UPDATE `#__categories` SET `modified_time` = `created_time`, `modified_user_id` = `created_user_id` WHERE `modified_time` = '0000-00-00 00:00:00';

UPDATE `#__categories` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_created_time` = '1980-01-01 00:00:00'
 WHERE `core_type_alias` IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND `core_created_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias` IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
 WHERE `core_type_alias`IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND `core_checked_out_time` = '0000-00-00 00:00:00';

-- From 4.0.0-2019-10-06.sql
ALTER TABLE `#__extensions` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__extensions` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
