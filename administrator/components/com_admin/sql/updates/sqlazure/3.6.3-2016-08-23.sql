-- Add default value for custom_data and system_data fields in the #__extensions table.
ALTER TABLE [#__extensions] ALTER COLUMN [custom_data] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__extensions] ALTER COLUMN [system_data] [nvarchar](max) NOT NULL DEFAULT '';
-- Add default value for data field in the #__updates table.
ALTER TABLE [#__updates] ALTER COLUMN [data] [nvarchar](max) NOT NULL DEFAULT '';
-- Add default value for asset_id field in the #__languages table.
ALTER TABLE [#__languages] ALTER COLUMN [asset_id] [bigint] NOT NULL DEFAULT 0;
-- Add default value for asset_id field in the #__menu_types table.
ALTER TABLE [#__menu_types] ALTER COLUMN [asset_id] [bigint] NOT NULL DEFAULT 0;
