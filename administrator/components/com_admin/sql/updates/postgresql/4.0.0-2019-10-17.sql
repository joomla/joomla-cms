-- From 4.0.0-2019-10-13.sql
UPDATE "#__content_types"
   SET "field_mappings" = REPLACE("field_mappings",'"core_created_time":"registerdate"','"core_created_time":"registerDate"')
 WHERE "type_alias" = 'com_users.user';

-- From 4.0.0-2019-10-17.sql
ALTER TABLE "#__users" ALTER COLUMN "registerDate" DROP DEFAULT;

ALTER TABLE "#__users" ALTER COLUMN "lastvisitDate" DROP NOT NULL;
ALTER TABLE "#__users" ALTER COLUMN "lastvisitDate" DROP DEFAULT;

ALTER TABLE "#__users" ALTER COLUMN "lastResetTime" DROP NOT NULL;
ALTER TABLE "#__users" ALTER COLUMN "lastResetTime" DROP DEFAULT;

UPDATE "#__users" SET "registerDate" = '1980-01-01 00:00:00' WHERE "registerDate" = '1970-01-01 00:00:00';
UPDATE "#__users" SET "lastvisitDate" = NULL WHERE "lastvisitDate" = '1970-01-01 00:00:00';
UPDATE "#__users" SET "lastResetTime" = NULL WHERE "lastResetTime" = '1970-01-01 00:00:00';

UPDATE "#__content_types"
   SET "field_mappings" = REPLACE("field_mappings",'"core_modified_time":"lastvisitDate"','"core_modified_time":"null"')
 WHERE "type_alias" = 'com_users.user';
