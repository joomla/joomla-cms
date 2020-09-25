DROP INDEX [idx_home] ON [#__template_styles];
# Query removed, see https://github.com/joomla/joomla-cms/pull/25484
CREATE NONCLUSTERED INDEX [idx_client_id] ON [#__template_styles]
(
  [client_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
CREATE NONCLUSTERED INDEX [idx_client_id_home] ON [#__template_styles]
(
  [client_id] ASC,
  [home] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
ALTER TABLE [#__template_styles] ADD DEFAULT (0) FOR [home];
