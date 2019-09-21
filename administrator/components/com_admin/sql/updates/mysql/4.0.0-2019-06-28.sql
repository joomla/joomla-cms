ALTER TABLE `#__banners` MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `#__banners` MODIFY `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `#__banners` MODIFY `reset` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `#__banners` MODIFY `publish_up` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `publish_down` datetime NULL DEFAULT NULL;
ALTER TABLE `#__banners` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__banners` SET `created` = '2005-08-17 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `reset` = `created` WHERE `reset` = '0000-00-00 00:00:00';

 UPDATE `#__banners` SET
	`publish_up` = CASE WHEN `publish_up` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `publish_up` END,
	`publish_down` = CASE WHEN `publish_down` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `publish_down` END,
	`checked_out_time` = CASE WHEN `checked_out_time` IN ('0000-00-00 00:00:00', '1000-01-01 00:00:00') THEN NULL ELSE `checked_out_time` END;