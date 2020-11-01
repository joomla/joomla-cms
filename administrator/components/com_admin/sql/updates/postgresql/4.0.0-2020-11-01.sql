UPDATE "#__banners" SET "created" = '1980-01-01 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__categories" SET "created_time" = '1980-01-01 00:00:00' WHERE "created_time" = '1970-01-01 00:00:00';
UPDATE "#__contact_details" SET "created" = '1980-01-01 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "created" = '1980-01-01 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__newsfeeds" SET "created" = '1980-01-01 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__tags" SET "created_time" = '1980-01-01 00:00:00' WHERE "created_time" = '1970-01-01 00:00:00';
UPDATE "#__user_notes" SET "created_time" = '1980-01-01 00:00:00' WHERE "created_time" = '1970-01-01 00:00:00';

UPDATE "#__ucm_content" SET "core_created_time" = '1980-01-01 00:00:00'
 WHERE "core_type_alias"
    IN ('com_banners.banner'
       ,'com_banners.category'
       ,'com_contact.category'
       ,'com_contact.contact'
       ,'com_content.article'
       ,'com_content.category'
       ,'com_newsfeeds.category'
       ,'com_newsfeeds.newsfeed'
       ,'com_tags.tag'
       ,'com_users.category'
       ,'com_users.note')
   AND "core_created_time" = '1970-01-01 00:00:00';

UPDATE "#__fields" SET "created_time" = '1980-01-01 00:00:00' WHERE "created_time" = '1970-01-01 00:00:00';
UPDATE "#__fields_groups" SET "created" = '1980-01-01 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__redirect_links" SET "created_date" = '1980-01-01 00:00:00' WHERE "created_date" = '1970-01-01 00:00:00';
UPDATE "#__users" SET "registerDate" = '1980-01-01 00:00:00' WHERE "registerDate" = '1970-01-01 00:00:00';
