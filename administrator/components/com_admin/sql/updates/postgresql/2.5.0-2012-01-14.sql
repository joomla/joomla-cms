ALTER TABLE "#__languages" ALTER COLUMN "sitename" TYPE character varying(1024),
ALTER COLUMN "sitename" SET DEFAULT '',
ALTER COLUMN "sitename" SET NOT NULL;
