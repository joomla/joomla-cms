DROP INDEX [idx_home] ON [#__template_styles];
CREATE NONCLUSTERED INDEX [idx_client_id] ON [#__template_styles]
(
  [client_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
# Queries removed, see https://github.com/joomla/joomla-cms/pull/25484
