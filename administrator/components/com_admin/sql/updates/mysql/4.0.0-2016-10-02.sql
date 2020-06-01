ALTER TABLE `#__user_keys` DROP COLUMN `invalid`;
ALTER TABLE `#__user_notes` CHANGE `modified_user_id` `modified_user_id` int(10) unsigned NOT NULL DEFAULT 0;

ALTER TABLE `#__users` MODIFY `registerDate` datetime NOT NULL;
ALTER TABLE `#__users` MODIFY `lastvisitDate` datetime NULL DEFAULT NULL;
ALTER TABLE `#__users` MODIFY `lastResetTime` datetime NULL DEFAULT NULL;

UPDATE `#__users` SET `lastvisitDate` = NULL WHERE `lastvisitDate` = '0000-00-00 00:00:00';
UPDATE `#__users` SET `lastResetTime` = NULL WHERE `lastResetTime` = '0000-00-00 00:00:00';
