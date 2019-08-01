ALTER TABLE "#__content" ALTER COLUMN "created" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "created" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "modified" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "modified" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "publish_up" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "publish_up" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "publish_down" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "publish_down" DROP NOT NULL;

UPDATE "#__content" SET "created" = NULL WHERE "created" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "modified" = NULL WHERE "modified" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "publish_up" = NULL WHERE "publish_up" = '1970-01-01 00:00:00';
UPDATE "#__content" SET "publish_down" = NULL WHERE "publish_down" = '1970-01-01 00:00:00';
