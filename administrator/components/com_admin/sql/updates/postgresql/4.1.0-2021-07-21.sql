DROP TABLE IF EXISTS "#__cronjobs_scripts";
ALTER TABLE "#__cronjobs"
    ALTER COLUMN "type" TYPE varchar(1024),
    ALTER COLUMN "type" SET NOT NULL,
    ADD COLUMN "params" text NOT NULL;
