DROP INDEX [idx_home] ON [#__template_styles];
ALTER TABLE [#__template_styles] ALTER COLUMN [home] [tyinint] NOT NULL;
CREATE NONCLUSTERED INDEX [idx_home] ON [#__template_styles]
(
	[home] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
ALTER TABLE [#__template_styles] ADD DEFAULT (0) FOR [home];
