ALTER TABLE "#__extensions" ALTER COLUMN "custom_data" DROP DEFAULT;
ALTER TABLE "#__extensions" ALTER COLUMN "system_data" DROP DEFAULT;
ALTER TABLE "#__updates" ALTER COLUMN "data" DROP DEFAULT;

ALTER TABLE "#__newsfeeds" ALTER COLUMN "xreference" SET DEFAULT '';
