-- Add default value for custom_data and system_data fields in the #__extensions table.
ALTER TABLE [#__extensions] ALTER COLUMN [custom_data] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__extensions] ALTER COLUMN [system_data] [nvarchar](max) NOT NULL DEFAULT '';
-- Add default value for data field in the #__updates table.
ALTER TABLE [#__updates] ALTER COLUMN [data] [nvarchar](max) NOT NULL DEFAULT '';
