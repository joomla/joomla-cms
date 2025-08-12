UPDATE "#__content_types"
SET "field_mappings" = jsonb_set("field_mappings"::jsonb, '{common, core_created_user_id}' , '"created_by"', true)
WHERE "type_alias" = 'com_content.article';

UPDATE "#__content_types"
SET "field_mappings" = jsonb_set("field_mappings"::jsonb, '{common, core_created_by_alias}' , '"created_by_alias"', true)
WHERE "type_alias" = 'com_content.article';
