-- The following two statements were modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE `#__menu` ADD COLUMN `publish_up` datetime /** CAN FAIL **/;
ALTER TABLE `#__menu` ADD COLUMN `publish_down` datetime /** CAN FAIL **/;

ALTER TABLE `#__menu` MODIFY `checked_out_time` datetime NULL DEFAULT NULL;

UPDATE `#__menu` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
