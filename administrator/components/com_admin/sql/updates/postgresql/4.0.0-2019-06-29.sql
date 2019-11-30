ALTER TABLE "#__newsfeeds" ALTER COLUMN "created" DROP DEFAULT;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "modified" DROP DEFAULT;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_up" DROP DEFAULT;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_down" DROP DEFAULT;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__newsfeeds" SET "modified" = "created", "modified_by" = "created_by" WHERE "modified" = '1970-01-01 00:00:00';

UPDATE "#__newsfeeds" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__newsfeeds" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__newsfeeds" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_modified_time" = "core_created_time"
 WHERE "core_type_alias" = 'com_newsfeeds.newsfeed'
   AND "core_modified_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_publish_up" = NULL
 WHERE "core_type_alias" = 'com_newsfeeds.newsfeed'
   AND "core_publish_up" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_publish_down" = NULL
 WHERE "core_type_alias" = 'com_newsfeeds.newsfeed'
   AND "core_publish_down" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_checked_out_time" = NULL
 WHERE "core_type_alias" = 'com_newsfeeds.newsfeed'
   AND "core_checked_out_time" = '1970-01-01 00:00:00';
