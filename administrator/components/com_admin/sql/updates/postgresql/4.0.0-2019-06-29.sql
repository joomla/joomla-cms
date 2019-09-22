ALTER TABLE "#__newsfeeds" ALTER COLUMN "created" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "created" SET NOT NULL;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "modified" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "modified" SET NOT NULL;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_up" DROP DEFAULT;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_down" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "publish_down" DROP DEFAULT;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__newsfeeds" SET "created" = '2005-08-17 00:00:00' WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__newsfeeds" SET "modified" = "created" WHERE "modified" = '1970-01-01 00:00:00';

UPDATE "#__newsfeeds" SET
	"publish_up" = CASE WHEN "publish_up" = '1970-01-01 00:00:00' THEN NULL ELSE "publish_up" END,
	"publish_down" = CASE WHEN "publish_down" = '1970-01-01 00:00:00' THEN NULL ELSE "publish_down" END,
	"checked_out_time" = CASE WHEN "checked_out_time" = '1970-01-01 00:00:00' THEN NULL ELSE "checked_out_time" END;