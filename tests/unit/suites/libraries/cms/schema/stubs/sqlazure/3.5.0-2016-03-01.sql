ALTER TABLE [#__redirect_links] DROP CONSTRAINT [#__redirect_links$idx_link_old];
ALTER TABLE [#__redirect_links] ALTER COLUMN [old_url] [nvarchar](2048) NOT NULL;
ALTER TABLE [#__redirect_links] ALTER COLUMN [new_url] [nvarchar](2048) NOT NULL;
ALTER TABLE [#__redirect_links] ALTER COLUMN [referer] [nvarchar](2048) NOT NULL;
CREATE NONCLUSTERED INDEX [idx_old_url] ON [#__redirect_links]
(
	[old_url] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
