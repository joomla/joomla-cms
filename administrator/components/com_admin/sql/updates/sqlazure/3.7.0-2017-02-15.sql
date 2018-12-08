-- Normalize redirect_links table default values.
ALTER TABLE [#__redirect_links] ADD DEFAULT ('') FOR [comment];
