DELETE FROM [#__extensions] WHERE [type] = 'library' AND [element] = 'simplepie';

SET IDENTITY_INSERT [#__extensions] ON;

INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 455, 'plg_installer_packageinstaller', 'plugin', 'packageinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 456, 'plg_installer_folderinstaller', 'plugin', 'folderinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 457, 'plg_installer_urlinstaller', 'plugin', 'urlinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 3, 0;

SET IDENTITY_INSERT [#__extensions] OFF;
