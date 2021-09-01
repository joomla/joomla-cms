ALTER TABLE `#__contact_details` MODIFY `created` datetime NOT NULL;
ALTER TABLE `#__contact_details` MODIFY `modified` datetime NOT NULL;

ALTER TABLE `#__contact_details` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__contact_details` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__contact_details` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__contact_details` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `modified` = `created`, `modified_by` = `created_by` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__contact_details` SET `publish_up` = NULL WHERE `publish_up` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `publish_down` = NULL WHERE `publish_down` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_created_time` = '1980-01-01 00:00:00'
 WHERE `core_type_alias` = 'com_contact.contact'
   AND `core_created_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias` = 'com_contact.contact'
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_publish_up` = NULL
 WHERE `core_type_alias` = 'com_contact.contact'
   AND `core_publish_up` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_publish_down` = NULL
 WHERE `core_type_alias` = 'com_contact.contact'
   AND `core_publish_down` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
 WHERE `core_type_alias` = 'com_contact.contact'
   AND `core_checked_out_time` = '0000-00-00 00:00:00';
