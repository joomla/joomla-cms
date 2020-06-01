ALTER TABLE `#__ucm_content` MODIFY `core_created_time` datetime NOT NULL;
ALTER TABLE `#__ucm_content` MODIFY `core_modified_time` datetime NOT NULL;
ALTER TABLE `#__ucm_content` MODIFY `core_publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__ucm_content` MODIFY `core_publish_down` datetime NULL DEFAULT NULL;

-- Only on MySQL: Update empty strings to null date before converting the column from varchar to datetime
UPDATE `#__ucm_content` SET `core_checked_out_time` = '0000-00-00 00:00:00' WHERE `core_checked_out_time` = '';
ALTER TABLE `#__ucm_content` MODIFY `core_checked_out_time` datetime;

ALTER TABLE `#__banners` MODIFY `created` datetime NOT NULL;
ALTER TABLE `#__banners` MODIFY `modified` datetime NOT NULL;
ALTER TABLE `#__banners` MODIFY `reset` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banner_clients` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;
UPDATE `#__banners` SET `modified` = `created`, `modified_by` = `created_by` WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `reset` = NULL WHERE `reset` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
UPDATE `#__banner_clients` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__categories` MODIFY `created_time` datetime NOT NULL;
ALTER TABLE `#__categories` MODIFY `modified_time` datetime NOT NULL;
ALTER TABLE `#__categories` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;
UPDATE `#__categories` SET `modified_time` = `created_time`, `modified_user_id` = `created_user_id` WHERE `modified_time` = '0000-00-00 00:00:00';
UPDATE `#__categories` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__contact_details` MODIFY `created` datetime NOT NULL;
ALTER TABLE `#__contact_details` MODIFY `modified` datetime NOT NULL;
ALTER TABLE `#__contact_details` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__contact_details` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__contact_details` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;
UPDATE `#__contact_details` SET `modified` = `created`, `modified_by` = `created_by` WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__content` MODIFY `created` datetime NOT NULL;
ALTER TABLE `#__content` MODIFY `modified` datetime NOT NULL;
ALTER TABLE `#__content` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__content` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__content` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;
UPDATE `#__content` SET `modified` = `created`, `modified_by` = `created_by` WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__newsfeeds` MODIFY `created` datetime NOT NULL;
ALTER TABLE `#__newsfeeds` MODIFY `modified` datetime NOT NULL;
ALTER TABLE `#__newsfeeds` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__newsfeeds` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__newsfeeds` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;
UPDATE `#__newsfeeds` SET `modified` = `created`, `modified_by` = `created_by` WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__newsfeeds` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__newsfeeds` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__newsfeeds` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__tags` MODIFY `created_time` datetime NOT NULL;
ALTER TABLE `#__tags` MODIFY `modified_time` datetime NOT NULL;
ALTER TABLE `#__tags` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__tags` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__tags` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;
UPDATE `#__tags` SET `modified_time` = `created_time`, `modified_user_id` = `created_user_id` WHERE `modified_time` = '0000-00-00 00:00:00';
UPDATE `#__tags` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__tags` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__tags` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

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
WHERE `core_type_alias` IN (
    'com_banners.banner',
    'com_banners.category',
    'com_contact.category',
    'com_contact.contact',
    'com_content.article',
    'com_content.category',
    'com_newsfeeds.category',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.category',
    'com_users.note')
  AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_publish_up` = NULL
WHERE `core_type_alias` IN (
    'com_banners.banner',
    'com_contact.contact',
    'com_content.article',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.note')
  AND `core_publish_up` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_publish_down` = NULL
WHERE `core_type_alias` IN (
    'com_banners.banner',
    'com_contact.contact',
    'com_content.article',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.note')
  AND `core_publish_down` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
WHERE `core_type_alias`IN (
    'com_banners.banner',
    'com_banners.category',
    'com_contact.category',
    'com_contact.contact'
    'com_content.article',
    'com_content.category',
    'com_newsfeeds.category',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.category',
    'com_users.note')
  AND `core_checked_out_time` = '0000-00-00 00:00:00';
