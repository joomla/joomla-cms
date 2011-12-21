ALTER TABLE "#__modules" ALTER COLUMN "position" TYPE character varying(50),
ALTER COLUMN "position" SET DEFAULT '',
ALTER COLUMN "position" SET NOT NULL;