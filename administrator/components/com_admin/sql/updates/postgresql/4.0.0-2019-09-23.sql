ALTER TABLE "#__redirect_links" ALTER COLUMN "created_date" DROP DEFAULT;
ALTER TABLE "#__redirect_links" ALTER COLUMN "modified_date" DROP DEFAULT;

UPDATE "#__redirect_links" SET "modified_date" = "created_date" WHERE "modified_date" = '1970-01-01 00:00:00';
