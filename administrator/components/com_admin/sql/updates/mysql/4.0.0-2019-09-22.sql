-- From 4.0.0-2019-09-14.sql
ALTER TABLE `#__content` MODIFY `created` datetime NOT NULL;
ALTER TABLE `#__content` MODIFY `modified` datetime NOT NULL;

ALTER TABLE `#__content` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__content` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__content` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__content` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `modified` = `created`, `modified_by` = `created_by` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__content` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_created_time` = '1980-01-01 00:00:00'
 WHERE `core_type_alias` = 'com_content.article'
   AND `core_created_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias` = 'com_content.article'
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_publish_up` = NULL
 WHERE `core_type_alias` = 'com_content.article'
   AND `core_publish_up` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_publish_down` = NULL
 WHERE `core_type_alias` = 'com_content.article'
   AND `core_publish_down` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
 WHERE `core_type_alias` = 'com_content.article'
   AND `core_checked_out_time` = '0000-00-00 00:00:00';

-- From 4.0.0-2019-09-22.sql
ALTER TABLE `#__messages` MODIFY `date_time` datetime NOT NULL;
