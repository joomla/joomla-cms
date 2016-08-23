-- Add default value for custom_data and system_data.
ALTER TABLE [#__extensions] ALTER COLUMN [custom_data] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__extensions] ALTER COLUMN [system_data] [nvarchar](max) NOT NULL DEFAULT '';
