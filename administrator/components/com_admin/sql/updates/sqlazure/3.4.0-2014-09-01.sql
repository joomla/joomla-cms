SET IDENTITY_INSERT [#__extensions] ON;

INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 801, 'weblinks', 'package', 'pkg_weblinks', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT [#__extensions] OFF;

SET IDENTITY_INSERT [#__update_sites] ON;

INSERT INTO [#__update_sites] ([name], [type], [location], [enabled])
SELECT 'Weblinks Update Site', 'extension', 'https://raw.githubusercontent.com/joomla-extensions/weblinks/master/manifest.xml', 1;

SET IDENTITY_INSERT [#__update_sites] OFF;

SET IDENTITY_INSERT [#__update_sites_extensions] ON;

INSERT INTO [#__update_sites_extensions] ([update_site_id], [extension_id])
SELECT (SELECT [update_site_id] FROM [#__update_sites] WHERE [name] = 'Weblinks Update Site'), 801;

SET IDENTITY_INSERT [#__update_sites_extensions] OFF;
