DROP INDEX [username] ON [#__users];

CREATE UNIQUE INDEX [idx_username] ON [#__users]
(
  [username] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);