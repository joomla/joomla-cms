ALTER TABLE "#__action_logs" ALTER COLUMN "log_date" SET DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE "#__action_logs" ALTER COLUMN "log_date" SET NOT NULL;

UPDATE "#__action_logs" SET "log_date" = '2005-08-17 00:00:00' WHERE "created_date" = '1970-01-01 00:00:00';
