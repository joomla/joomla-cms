ALTER TABLE `#__users` MODIFY `registerDate` datetime NOT NULL;

ALTER TABLE `#__users` MODIFY `lastvisitDate` datetime NULL DEFAULT NULL;
ALTER TABLE `#__users` MODIFY `lastResetTime` datetime NULL DEFAULT NULL;

UPDATE `#__users` SET `lastvisitDate` = NULL WHERE `lastvisitDate` = '0000-00-00 00:00:00';
UPDATE `#__users` SET `lastResetTime` = NULL WHERE `lastResetTime` = '0000-00-00 00:00:00';

UPDATE `#__content_types`
   SET `field_mappings` = REPLACE(`field_mappings`,'"core_modified_time":"lastvisitDate"','"core_modified_time":"null"')
 WHERE `type_alias` = 'com_users.user';
