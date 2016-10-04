ALTER TABLE "#__finder_links" ALTER COLUMN "title" TYPE character varying(400);
ALTER TABLE "#__finder_links" ALTER COLUMN "description" TYPE text DEFAULT '' NOT NULL;
