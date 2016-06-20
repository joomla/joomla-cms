ALTER TABLE "#__redirect_links" ADD COLUMN "header" INTEGER DEFAULT 301 NOT NULL;
--
-- The following statement has to be disabled because it conflicts with
-- a later change added with Joomla! 3.5.0 for long URLs in this table
--
-- ALTER TABLE "#__redirect_links" ALTER COLUMN "new_url" DROP NOT NULL;
