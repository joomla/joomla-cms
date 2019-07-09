DROP INDEX [idx_client_id_home] ON [#__template_styles];
ALTER TABLE [#__template_styles] ALTER COLUMN [home] nvarchar(7) NOT NULL;
ALTER TABLE [#__template_styles] ADD DEFAULT ('0') FOR [home];
CREATE NONCLUSTERED INDEX [idx_client_id_home_2] ON [#__template_styles]
(
  [client_id] ASC,
  [home] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
