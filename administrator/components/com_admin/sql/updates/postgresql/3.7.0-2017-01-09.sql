-- Normalize categories table default values.
ALTER TABLE "#__categories" ALTER COLUMN "title" SET DEFAULT '';
--
-- The following statement has to be disabled because it conflicts with
-- a later change added with Joomla! 3.9.16, see file 3.9.16-2020-02-15.sql
--
-- ALTER TABLE "#__categories" ALTER COLUMN "params" SET DEFAULT '';
ALTER TABLE "#__categories" ALTER COLUMN "metadesc" SET DEFAULT '';
ALTER TABLE "#__categories" ALTER COLUMN "metakey" SET DEFAULT '';
ALTER TABLE "#__categories" ALTER COLUMN "metadata" SET DEFAULT '';
