ALTER TABLE [#__redirect_links] ADD [hits] INTEGER CONSTRAINT DF_redirect_links_hits DEFAULT '' NOT NULL;
ALTER TABLE [#__users] ADD [lastResetTime] [datetime] NOT NULL;
ALTER TABLE [#__users] ADD [resetCount] [int] NOT NULL;