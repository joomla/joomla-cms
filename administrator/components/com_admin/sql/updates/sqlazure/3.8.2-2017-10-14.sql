--
-- Add index for alias check #__content
--

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__content]
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
