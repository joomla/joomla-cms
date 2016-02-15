ALTER TABLE [#__redirect_links] ADD [header] [smallint] NOT NULL DEFAULT 301;
ALTER TABLE [#__redirect_links] ALTER COLUMN [new_url] [nvarchar](255) NULL;