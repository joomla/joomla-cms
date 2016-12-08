ALTER TABLE [#__extensions] ADD [package_id] [bigint] NOT NULL DEFAULT 0;

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_en-GB')
WHERE [type]= 'language' AND [element] = 'en-GB';
