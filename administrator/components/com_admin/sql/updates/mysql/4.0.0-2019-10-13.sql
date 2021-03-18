UPDATE `#__content_types`
   SET `field_mappings` = REPLACE(`field_mappings`,'"core_created_time":"registerdate"','"core_created_time":"registerDate"')
 WHERE `type_alias` = 'com_users.user';
