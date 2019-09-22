ALTER TABLE "#__redirect_links" ALTER COLUMN "created_date" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__redirect_links" ALTER COLUMN "created_date" SET NOT NULL;

ALTER TABLE "#__redirect_links" ALTER COLUMN "modified_date" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__redirect_links" ALTER COLUMN "modified_date" SET NOT NULL;

UPDATE "#__redirect_links" SET "created_date" = '2005-08-17 00:00:00' WHERE "created_date" = '1970-01-01 00:00:00';
UPDATE "#__redirect_links" SET "modified_date" = "created_date" WHERE "modified_date" = '1970-01-01 00:00:00';
