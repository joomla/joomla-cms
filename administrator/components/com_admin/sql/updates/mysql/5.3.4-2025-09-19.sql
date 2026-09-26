UPDATE `#__content_types`
SET `field_mappings` = JSON_SET(`field_mappings`, '$.common.core_created_user_id', 'created_by')
WHERE `type_alias` = 'com_content.article';

UPDATE `#__content_types`
SET `field_mappings` = JSON_SET(`field_mappings`, '$.common.core_created_by_alias', 'created_by_alias')
WHERE `type_alias` = 'com_content.article';
