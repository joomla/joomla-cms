ALTER TABLE "#__associations" ADD COLUMN "parent_id" integer DEFAULT -1 NOT NULL;
ALTER TABLE "#__associations" ADD COLUMN "parent_date" timestamp without time zone;
COMMENT ON COLUMN "#__associations"."parent_id" IS 'The parent of an association.';
COMMENT ON COLUMN "#__associations"."parent_date" IS 'The save or modified date of the parent.';
