-- Normalize ucm_content_table default values.
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_type_alias] [nvarchar](255) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_body] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_params] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_metadata] [nvarchar](2048) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_language] [nvarchar](7) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_content_item_id] [bigint] NOT NULL DEFAULT 0;
ALTER TABLE [#__ucm_content] ALTER COLUMN [asset_id] [bigint] NOT NULL DEFAULT 0;
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_images] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_urls] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_metakey] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_metadesc] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_xreference] [nvarchar](50) NOT NULL DEFAULT '';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_type_id] [bigint] NOT NULL DEFAULT 0;
