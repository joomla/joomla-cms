-- Normalize categories table default values.
ALTER TABLE [#__categories] ALTER COLUMN [title] [nvarchar](255) NOT NULL DEFAULT '';
ALTER TABLE [#__categories] ALTER COLUMN [description] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__categories] ALTER COLUMN [params] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__categories] ALTER COLUMN [metadesc] [nvarchar](1024) NOT NULL DEFAULT '';
ALTER TABLE [#__categories] ALTER COLUMN [metakey] [nvarchar](1024) NOT NULL DEFAULT '';
ALTER TABLE [#__categories] ALTER COLUMN [metadata] [nvarchar](2048) NOT NULL DEFAULT '';
ALTER TABLE [#__categories] ALTER COLUMN [language] [nvarchar](7) NOT NULL DEFAULT '';
