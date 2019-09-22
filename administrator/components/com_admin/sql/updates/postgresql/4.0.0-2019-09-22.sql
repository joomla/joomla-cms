ALTER TABLE "#__messages" ALTER COLUMN "date_time" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__messages" ALTER COLUMN "date_time" SET NOT NULL;

UPDATE "#__messages" SET "date_time" = '2005-08-17 00:00:00' WHERE "date_time" = '1970-01-01 00:00:00';
