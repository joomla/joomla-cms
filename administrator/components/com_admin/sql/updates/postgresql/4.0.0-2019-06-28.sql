ALTER TABLE "#__banners" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "publish_up" DROP DEFAULT;

ALTER TABLE "#__banners" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "publish_down" DROP DEFAULT;

ALTER TABLE "#__banners" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__banners" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__banners" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
UPDATE "#__banners" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';
