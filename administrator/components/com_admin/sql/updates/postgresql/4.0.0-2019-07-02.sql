ALTER TABLE "#__associations" ADD COLUMN "master_id" integer DEFAULT -1 NOT NULL;
ALTER TABLE "#__associations" ADD COLUMN "master_date" timestamp without time zone;
COMMENT ON COLUMN "#__associations"."master_id" IS 'The master item of an association.';
COMMENT ON COLUMN "#__associations"."master_date" IS 'The save or modified date of the master item.';
