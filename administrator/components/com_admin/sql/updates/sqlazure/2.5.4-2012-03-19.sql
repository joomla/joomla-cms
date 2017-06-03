ALTER TABLE [#__languages] ADD  [access] INTEGER CONSTRAINT DF_languages_access DEFAULT '' NOT NULL

CREATE UNIQUE INDEX idx_access ON [#__languages] (access);

UPDATE [#__categories] SET [extension] = 'com_users.notes' WHERE [extension] = 'com_users';

UPDATE [#__extensions] SET [enabled] = '1' WHERE [protected] = '1' AND [type] <> 'plugin';
