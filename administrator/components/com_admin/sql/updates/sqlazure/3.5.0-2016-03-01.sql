ALTER TABLE [#__redirect_links] DROP CONSTRAINT [#__redirect_links$idx_link_old];
ALTER TABLE [#__redirect_links] ALTER COLUMN [old_url] [nvarchar](2048) NOT NULL;
ALTER TABLE [#__redirect_links] ALTER COLUMN [new_url] [nvarchar](2048) NOT NULL;
ALTER TABLE [#__redirect_links] ALTER COLUMN [referer] [nvarchar](2048) NOT NULL;
ALTER TABLE [#__redirect_links] ADD CONSTRAINT [#__redirect_links$idx_old_url] NONCLUSTERED
(
	[old_url] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY];
