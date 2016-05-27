ALTER TABLE [#__redirect_links] ALTER COLUMN [new_url] [nvarchar](2048);
UPDATE [#__languages] SET [access] = 1 WHERE [title] = 'English (UK)' AND [access] = 0;
