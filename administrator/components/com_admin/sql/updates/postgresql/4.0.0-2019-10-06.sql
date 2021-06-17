-- From 4.0.0-2019-09-23.sql
ALTER TABLE "#__redirect_links" ALTER COLUMN "created_date" DROP DEFAULT;
ALTER TABLE "#__redirect_links" ALTER COLUMN "modified_date" DROP DEFAULT;

UPDATE "#__redirect_links" SET "created_date" = '1980-01-01 00:00:00' WHERE "created_date" = '1970-01-01 00:00:00';
UPDATE "#__redirect_links" SET "modified_date" = "created_date" WHERE "modified_date" = '1970-01-01 00:00:00';

-- From 4.0.0-2019-09-24.sql
ALTER TABLE "#__action_logs" ALTER COLUMN "log_date" DROP DEFAULT;

-- From 4.0.0-2019-09-25.sql
ALTER TABLE "#__fields" ALTER COLUMN "created_time" DROP DEFAULT;
ALTER TABLE "#__fields" ALTER COLUMN "modified_time" DROP DEFAULT;
ALTER TABLE "#__fields" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__fields" ALTER COLUMN "checked_out_time" DROP DEFAULT;

ALTER TABLE "#__fields_groups" ALTER COLUMN "created" DROP DEFAULT;
ALTER TABLE "#__fields_groups" ALTER COLUMN "modified" DROP DEFAULT;
ALTER TABLE "#__fields_groups" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__fields_groups" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__fields" SET "modified_time" = "created_time", "modified_by" = "created_user_id" WHERE "modified_time" = '1970-01-01 00:00:00';
UPDATE "#__fields" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

UPDATE "#__fields_groups" SET "modified" = "created", "modified_by" = "created_by" WHERE "modified" = '1970-01-01 00:00:00';
UPDATE "#__fields_groups" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

-- From 4.0.0-2019-09-26.sql
ALTER TABLE "#__privacy_requests" ALTER COLUMN "requested_at" DROP DEFAULT;

ALTER TABLE "#__privacy_requests" ALTER COLUMN "confirm_token_created_at" DROP NOT NULL;
ALTER TABLE "#__privacy_requests" ALTER COLUMN "confirm_token_created_at" DROP DEFAULT;

ALTER TABLE "#__privacy_consents" ALTER COLUMN "created" DROP DEFAULT;

UPDATE "#__privacy_requests" SET "confirm_token_created_at" = NULL WHERE "confirm_token_created_at" = '1970-01-01 00:00:00';

-- From 4.0.0-2019-09-27.sql
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

-- From 4.0.0-2019-09-28.sql
ALTER TABLE "#__user_notes" ALTER COLUMN "created_time" DROP DEFAULT;

ALTER TABLE "#__user_notes" ALTER COLUMN "modified_time" DROP DEFAULT;

ALTER TABLE "#__user_notes" ALTER COLUMN "review_time" DROP NOT NULL;
ALTER TABLE "#__user_notes" ALTER COLUMN "review_time" DROP DEFAULT;

ALTER TABLE "#__user_notes" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__user_notes" ALTER COLUMN "publish_up" DROP DEFAULT;

ALTER TABLE "#__user_notes" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__user_notes" ALTER COLUMN "publish_down" DROP DEFAULT;

ALTER TABLE "#__user_notes" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__user_notes" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__user_notes" SET "modified_time" = "created_time", "modified_user_id" = "created_user_id" WHERE "modified_time" = '1970-01-01 00:00:00';

UPDATE "#__user_notes" SET "review_time" = NULL WHERE "review_time" = '1970-01-01 00:00:00';
UPDATE "#__user_notes" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__user_notes" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__user_notes" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_modified_time" = "core_created_time"
 WHERE "core_type_alias" = 'com_users.note'
   AND "core_modified_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_publish_up" = NULL
 WHERE "core_type_alias" = 'com_users.note'
   AND "core_publish_up" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_publish_down" = NULL
 WHERE "core_type_alias" = 'com_users.note'
   AND "core_publish_down" = '1970-01-01 00:00:00';
UPDATE "#__ucm_content" SET "core_checked_out_time" = NULL
 WHERE "core_type_alias" = 'com_users.note'
   AND "core_checked_out_time" = '1970-01-01 00:00:00';

-- From 4.0.0-2019-09-29.sql
ALTER TABLE "#__categories" ALTER COLUMN "created_time" DROP DEFAULT;

ALTER TABLE "#__categories" ALTER COLUMN "modified_time" DROP DEFAULT;

ALTER TABLE "#__categories" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__categories" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__categories" SET "created_time" = '1980-01-01 00:00:00' WHERE "created_time" = '1970-01-01 00:00:00';
UPDATE "#__categories" SET "modified_time" = "created_time", "modified_user_id" = "created_user_id" WHERE "modified_time" = '1970-01-01 00:00:00';

UPDATE "#__categories" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_created_time" = '1980-01-01 00:00:00'
 WHERE "core_type_alias" IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND "core_created_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_modified_time" = "core_created_time"
 WHERE "core_type_alias" IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND "core_modified_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_checked_out_time" = NULL
 WHERE "core_type_alias" IN ('com_content.category', 'com_contact.category', 'com_newsfeeds.category', 'com_banners.category', 'com_users.category')
   AND "core_checked_out_time" = '1970-01-01 00:00:00';

-- From 4.0.0-2019-10-06.sql
ALTER TABLE "#__extensions" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__extensions" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__extensions" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';
