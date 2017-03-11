-- Normalize categories table default values.
ALTER TABLE [#__categories] ADD DEFAULT ('') FOR [title];
ALTER TABLE [#__categories] ADD DEFAULT ('') FOR [description];
ALTER TABLE [#__categories] ADD DEFAULT ('') FOR [params];
ALTER TABLE [#__categories] ADD DEFAULT ('') FOR [metadesc];
ALTER TABLE [#__categories] ADD DEFAULT ('') FOR [metakey];
ALTER TABLE [#__categories] ADD DEFAULT ('') FOR [metadata];
ALTER TABLE [#__categories] ADD DEFAULT ('') FOR [language];
