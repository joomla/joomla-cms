ALTER TABLE "#__finder_links" ALTER COLUMN "title" TYPE character varying(400);
ALTER TABLE "#__finder_links" ALTER COLUMN "description" TYPE text;
--
-- The following statement has to be disabled because it conflicts with
-- a later change added with Joomla! 3.9.16, see file 3.9.16-2020-02-15.sql
--
-- ALTER TABLE "#__finder_links" ALTER COLUMN "description" SET NOT NULL;
