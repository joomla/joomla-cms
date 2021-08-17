INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'search', 'package', 'pkg_search', '', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0);

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_search')
WHERE [type]= 'component' AND [element] = 'com_search';

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_search')
WHERE [type]= 'module' AND [element] = 'mod_search' AND [client_id] = 0;

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_search')
WHERE [type]= 'plugin' AND [folder] = 'search' AND [element] = 'categories';

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_search')
WHERE [type]= 'plugin' AND [folder] = 'search' AND [element] = 'contacts';

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_search')
WHERE [type]= 'plugin' AND [folder] = 'search' AND [element] = 'content';

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_search')
WHERE [type]= 'plugin' AND [folder] = 'search' AND [element] = 'newsfeeds';

UPDATE [#__extensions]
SET [package_id] = (SELECT [extension_id] FROM [#__extensions] WHERE [type] = 'package' AND [element] = 'pkg_search')
WHERE [type]= 'plugin' AND [folder] = 'search' AND [element] = 'tags';
