ALTER TABLE "#__modules" ALTER COLUMN "position" TYPE character varying(50);
ALTER TABLE "#__modules" ALTER COLUMN "position" SET DEFAULT '';
ALTER TABLE "#__modules" ALTER COLUMN "position" SET NOT NULL;
