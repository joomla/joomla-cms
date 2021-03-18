ALTER TABLE "#__tags" ALTER COLUMN "created_time" DROP DEFAULT;

ALTER TABLE "#__tags" ALTER COLUMN "modified_time" DROP DEFAULT;

ALTER TABLE "#__tags" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__tags" ALTER COLUMN "publish_up" DROP DEFAULT;

ALTER TABLE "#__tags" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__tags" ALTER COLUMN "publish_down" DROP DEFAULT;

ALTER TABLE "#__tags" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__tags" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__tags" SET "modified_time" = "created_time", "modified_user_id" = "created_user_id" WHERE "modified_time" = '1970-01-01 00:00:00';

UPDATE "#__tags" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__tags" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__tags" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_modified_time" = "core_created_time"
 WHERE "core_type_alias" = 'com_tags.tag'
   AND "core_modified_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_publish_up" = NULL
 WHERE "core_type_alias" = 'com_tags.tag'
   AND "core_publish_up" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_publish_down" = NULL
 WHERE "core_type_alias" = 'com_tags.tag'
   AND "core_publish_down" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_checked_out_time" = NULL
 WHERE "core_type_alias" = 'com_tags.tag'
   AND "core_checked_out_time" = '1970-01-01 00:00:00';
