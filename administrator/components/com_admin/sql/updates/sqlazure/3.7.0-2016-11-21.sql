-- Replace language image UNIQUE index for a normal INDEX.
ALTER TABLE [#__languages] DROP CONSTRAINT [#__languages$idx_image];
CREATE NONCLUSTERED INDEX [idx_image] ON [#__languages] (
	[image] ASC
) WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
