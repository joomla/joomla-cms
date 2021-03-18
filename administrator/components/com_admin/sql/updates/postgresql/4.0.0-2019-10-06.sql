ALTER TABLE "#__extensions" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__extensions" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__extensions" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';
