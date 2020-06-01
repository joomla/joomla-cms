UPDATE `#__content_types` SET `router` = '' WHERE `type_alias` = 'com_users.user';

UPDATE `#__content_types`
SET `field_mappings` = REPLACE(`field_mappings`,'"core_created_time":"registerdate"','"core_created_time":"registerDate"')
WHERE `type_alias` = 'com_users.user';

UPDATE `#__content_types`
SET `field_mappings` = REPLACE(`field_mappings`,'"core_modified_time":"lastvisitDate"','"core_modified_time":"null"')
WHERE `type_alias` = 'com_users.user';

ALTER TABLE `#__content_types` MODIFY `table` varchar(2048) NOT NULL DEFAULT '';
