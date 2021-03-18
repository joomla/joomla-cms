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
