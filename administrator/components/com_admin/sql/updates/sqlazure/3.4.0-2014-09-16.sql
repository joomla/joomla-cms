ALTER TABLE [#__redirect_links] ADD [header] [smallint] NOT NULL DEFAULT 301;
--
-- The following statement has to be disabled because it conflicts with
-- a later change added with Joomla! 3.5.0 for long URLs in this table
--
-- ALTER TABLE [#__redirect_links] ALTER COLUMN [new_url] [nvarchar](255) NULL;
