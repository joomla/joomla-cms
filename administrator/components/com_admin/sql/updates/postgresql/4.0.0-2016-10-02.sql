ALTER TABLE "#__user_keys" DROP COLUMN "invalid";
ALTER TABLE "#__user_notes" ALTER COLUMN "modified_user_id" SET DEFAULT 0;

ALTER TABLE "#__users" ALTER COLUMN "registerDate" DROP DEFAULT;

ALTER TABLE "#__users" ALTER COLUMN "lastvisitDate" DROP NOT NULL;
ALTER TABLE "#__users" ALTER COLUMN "lastvisitDate" DROP DEFAULT;

ALTER TABLE "#__users" ALTER COLUMN "lastResetTime" DROP NOT NULL;
ALTER TABLE "#__users" ALTER COLUMN "lastResetTime" DROP DEFAULT;

UPDATE "#__users" SET "lastvisitDate" = NULL WHERE "lastvisitDate" = '1970-01-01 00:00:00';
UPDATE "#__users" SET "lastResetTime" = NULL WHERE "lastResetTime" = '1970-01-01 00:00:00';
