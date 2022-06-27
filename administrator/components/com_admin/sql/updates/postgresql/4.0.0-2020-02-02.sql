-- From 4.0.0-2019-10-29.sql
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_webservices_installer', 'plugin', 'installer', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, NULL, 0, 0);

-- From 4.0.0-2019-11-07.sql
--
-- Joomla API authentication with token
--
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_user_token', 'plugin', 'token', 'user', 0, 1, 1, 0, '', '{}', '', 0, NULL, 0, 0),
(0, 'plg_api-authentication_token', 'plugin', 'token', 'api-authentication', 0, 1, 1, 0, '', '{}', '', 0, NULL, 0, 0);

--
-- Disable the less secure basic authentication
--
UPDATE "#__extensions" SET "enabled" = 0 WHERE "name" = 'plg_api-authentication_basic' AND "type" = 'plugin';

-- From 4.0.0-2019-11-19.sql
DELETE FROM "#__menu" WHERE "link" = 'index.php?option=com_messages' AND "menutype" = 'main';
DELETE FROM "#__menu" WHERE "link" = 'index.php?option=com_messages&view=messages' AND "menutype" = 'main';
DELETE FROM "#__menu" WHERE "link" = 'index.php?option=com_messages&task=message.add' AND "menutype" = 'main';

-- From 4.0.0-2020-02-02.sql
UPDATE "#__menu" SET "img" = 'class:bookmark' WHERE "path" = 'Banners';
UPDATE "#__menu" SET "img" = 'class:address-book' WHERE "path" = 'Contacts';
UPDATE "#__menu" SET "img" = 'class:rss' WHERE "path" = 'News Feeds';
UPDATE "#__menu" SET "img" = 'class:language' WHERE "path" = 'Multilingual Associations';
UPDATE "#__menu" SET "img" = 'class:search-plus' WHERE "path" = 'Smart Search';
