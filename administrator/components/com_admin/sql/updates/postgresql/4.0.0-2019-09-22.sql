-- From 4.0.0-2019-09-14.sql
ALTER TABLE "#__content" ALTER COLUMN "created" DROP DEFAULT;

ALTER TABLE "#__content" ALTER COLUMN "modified" DROP DEFAULT;

ALTER TABLE "#__content" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "publish_up" DROP DEFAULT;

ALTER TABLE "#__content" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "publish_down" DROP DEFAULT;

ALTER TABLE "#__content" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__content" SET "created" = '1980-01-01 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "modified" = "created", "modified_by" = "created_by" WHERE "modified" = '1970-01-01 00:00:00';

UPDATE "#__content" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_created_time" = '1980-01-01 00:00:00'
 WHERE "core_type_alias" = 'com_content.article'
   AND "core_created_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_modified_time" = "core_created_time"
 WHERE "core_type_alias" = 'com_content.article'
   AND "core_modified_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_publish_up" = NULL
 WHERE "core_type_alias" = 'com_content.article'
   AND "core_publish_up" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_publish_down" = NULL
 WHERE "core_type_alias" = 'com_content.article'
   AND "core_publish_down" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_checked_out_time" = NULL
 WHERE "core_type_alias" = 'com_content.article'
   AND "core_checked_out_time" = '1970-01-01 00:00:00';

-- From 4.0.0-2019-09-22.sql
ALTER TABLE "#__messages" ALTER COLUMN "date_time" DROP DEFAULT;
