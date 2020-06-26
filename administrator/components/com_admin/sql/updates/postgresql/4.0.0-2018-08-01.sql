ALTER TABLE "#__ucm_content" ALTER COLUMN "core_created_time" DROP DEFAULT;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_modified_time" DROP DEFAULT;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_up" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_up" DROP DEFAULT;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_down" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_publish_down" DROP DEFAULT;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_checked_out_time" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_checked_out_time" DROP DEFAULT;

ALTER TABLE "#__banners" ALTER COLUMN "created" DROP DEFAULT;
ALTER TABLE "#__banners" ALTER COLUMN "modified" DROP DEFAULT;
ALTER TABLE "#__banners" ALTER COLUMN "reset" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "reset" DROP DEFAULT;
ALTER TABLE "#__banners" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "publish_up" DROP DEFAULT;
ALTER TABLE "#__banners" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "publish_down" DROP DEFAULT;
ALTER TABLE "#__banners" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "checked_out_time" DROP DEFAULT;
ALTER TABLE "#__banner_clients" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__banner_clients" ALTER COLUMN "checked_out_time" DROP DEFAULT;
UPDATE "#__banners" SET "modified" = "created", "modified_by" = "created_by" WHERE "modified" = '1970-01-01 00:00:00';
UPDATE "#__banners" SET "reset" = NULL WHERE "reset" = '1970-01-01 00:00:00';
UPDATE "#__banners" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__banners" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__banners" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';
UPDATE "#__banner_clients" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

ALTER TABLE "#__categories" ALTER COLUMN "created_time" DROP DEFAULT;
ALTER TABLE "#__categories" ALTER COLUMN "modified_time" DROP DEFAULT;
ALTER TABLE "#__categories" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__categories" ALTER COLUMN "checked_out_time" DROP DEFAULT;
UPDATE "#__categories" SET "modified_time" = "created_time", "modified_user_id" = "created_user_id" WHERE "modified_time" = '1970-01-01 00:00:00';
UPDATE "#__categories" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

ALTER TABLE "#__contact_details" ALTER COLUMN "created" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "modified" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__contact_details" ALTER COLUMN "publish_up" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__contact_details" ALTER COLUMN "publish_down" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__contact_details" ALTER COLUMN "checked_out_time" DROP DEFAULT;
UPDATE "#__contact_details" SET "modified" = "created", "modified_by" = "created_by" WHERE "modified" = '1970-01-01 00:00:00';
UPDATE "#__contact_details" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__contact_details" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__contact_details" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

ALTER TABLE "#__content" ALTER COLUMN "created" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "modified" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "publish_up" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "publish_down" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "checked_out_time" DROP DEFAULT;
UPDATE "#__content" SET "modified" = "created", "modified_by" = "created_by" WHERE "modified" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';

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
WHERE "core_type_alias" IN (
    'com_banners.banner',
    'com_banners.category',
    'com_contact.category',
    'com_contact.contact',
    'com_content.article',
    'com_content.category',
    'com_newsfeeds.category',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.category',
    'com_users.note')
  AND "core_modified_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_publish_up" = NULL
WHERE "core_type_alias" IN (
    'com_banners.banner',
    'com_contact.contact',
    'com_content.article',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.note')
  AND "core_publish_up" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_publish_down" = NULL
WHERE "core_type_alias" IN (
    'com_banners.banner',
    'com_contact.contact',
    'com_content.article',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.note')
  AND "core_publish_down" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_checked_out_time" = NULL
WHERE "core_type_alias" IN (
    'com_banners.banner',
    'com_banners.category',
    'com_contact.category',
    'com_contact.contact'
    'com_content.article',
    'com_content.category',
    'com_newsfeeds.category',
    'com_newsfeeds.newsfeed',
    'com_tags.tag',
    'com_users.category',
    'com_users.note')
  AND "core_checked_out_time" = '1970-01-01 00:00:00';
