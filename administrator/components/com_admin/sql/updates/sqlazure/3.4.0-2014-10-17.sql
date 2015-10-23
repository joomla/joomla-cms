ALTER TABLE [#__redirect_links] DROP CONSTRAINT [#__redirect_links$idx_link_old];

ALTER TABLE [#__redirect_links] ALTER COLUMN [old_url] [nvarchar](2083) NOT NULL;
ALTER TABLE [#__redirect_links] ALTER COLUMN [new_url] [nvarchar](2083) NOT NULL;
ALTER TABLE [#__redirect_links] ALTER COLUMN [referer] [nvarchar](2083) NOT NULL;
