ALTER TABLE "#__banners" DROP COLUMN "alias";
UPDATE "#__content_types"
  SET "field_mappings" = REPLACE("field_mappings",',"core_alias":"alias"','')
  WHERE "type_alias" = 'com_banners.banner';
