ALTER TABLE `#__banners` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `#__banners` MODIFY `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `#__banners` MODIFY `reset` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

ALTER TABLE `#__banner_clients` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__banners` SET `created` = '2005-08-17 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00' OR `modified` < `created`;

UPDATE `#__banners` SET `reset` = NULL WHERE `reset` = '0000-00-00 00:00:00';
 UPDATE `#__banners` SET
	`publish_up` = CASE WHEN `publish_up` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `publish_up` END,
	`publish_down` = CASE WHEN `publish_down` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `publish_down` END,
	`checked_out_time` = CASE WHEN `checked_out_time` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `checked_out_time` END;

UPDATE `#__banner_clients` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_created_time` = '2005-08-17 00:00:00'
 WHERE `core_type_alias` = 'com_banners.banner'
   AND `core_created_time` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_modified_time` = '2005-08-17 00:00:00'
 WHERE `core_type_alias` = 'com_banners.banner'
   AND (`core_modified_time` = '0000-00-00 00:00:00' OR `core_modified_time` < `core_created_time`);

UPDATE `#__ucm_content` SET `core_publish_up` = NULL
 WHERE `core_type_alias` = 'com_banners.banner'
   AND `core_publish_up` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_publish_down` = NULL
 WHERE `core_type_alias` = 'com_banners.banner'
   AND `core_publish_down` = '0000-00-00 00:00:00';
UPDATE `#__ucm_content` SET `core_checked_out_time` = NULL
 WHERE `core_type_alias` = 'com_banners.banner'
   AND `core_checked_out_time` = '0000-00-00 00:00:00';
