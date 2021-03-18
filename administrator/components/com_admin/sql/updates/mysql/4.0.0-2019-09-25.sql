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
