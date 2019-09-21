ALTER TABLE "#__banners" ALTER COLUMN "created" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__banners" ALTER COLUMN "created" SET NOT NULL;

ALTER TABLE "#__banners" ALTER COLUMN "modified" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__banners" ALTER COLUMN "modified" SET NOT NULL;

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

UPDATE "#__banners" SET "created" = '2005-08-17 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__banners" SET "modified" = "created" WHERE "modified" = '1970-01-01 00:00:00';

UPDATE "#__banners" SET "reset" = NULL WHERE "reset" = '1970-01-01 00:00:00';
 UPDATE "#__banners" SET
	"publish_up" = CASE WHEN "publish_up" = '1970-01-01 00:00:00' THEN NULL ELSE "publish_up" END,
	"publish_down" = CASE WHEN "publish_down" = '1970-01-01 00:00:00' THEN NULL ELSE "publish_down" END,
	"checked_out_time" = CASE WHEN "checked_out_time" = '1970-01-01 00:00:00' THEN NULL ELSE "checked_out_time" END;

UPDATE "#__banner_clients" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';
