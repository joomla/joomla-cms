-- Normalize ucm_content_table default values.
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_type_alias];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_body];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_params];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_metadata];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_language];

ALTER TABLE [#__ucm_content] DROP CONSTRAINT [#__ucm_content_core_content_id$idx_type_alias_item_id];
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_content_item_id] [bigint] NOT NULL;
ALTER TABLE [#__ucm_content] ADD CONSTRAINT [#__ucm_content_core_content_id$idx_type_alias_item_id] UNIQUE NONCLUSTERED
(
	[core_type_alias] ASC,
	[core_content_item_id] ASC
) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY];
ALTER TABLE [#__ucm_content] ADD DEFAULT (0) FOR [core_content_item_id];

ALTER TABLE [#__ucm_content] ALTER COLUMN [asset_id] [bigint] NOT NULL;
ALTER TABLE [#__ucm_content] ADD DEFAULT (0) FOR [asset_id];

ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_images];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_urls];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_metakey];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_metadesc];
ALTER TABLE [#__ucm_content] ADD DEFAULT ('') FOR [core_xreference];

DROP INDEX [idx_core_type_id] ON [#__ucm_content];
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_type_id] [bigint] NOT NULL;
CREATE NONCLUSTERED INDEX [idx_core_type_id] ON [#__ucm_content]
(
	[core_type_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
ALTER TABLE [#__ucm_content] ADD DEFAULT (0) FOR [core_type_id];
