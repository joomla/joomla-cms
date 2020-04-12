--
-- Joomla API authentication with token
--
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_user_token', 'plugin', 'token', 'user', 0, 1, 1, 0, '', '{}', 0, NULL, 0, 0),
(0, 'plg_api-authentication_token', 'plugin', 'token', 'api-authentication', 0, 1, 1, 0, '', '{}', 0, NULL, 0, 0);

--
-- Disable the less secure basic authentication
--
UPDATE "#__extensions" SET "enabled" = 0 WHERE "name" = 'plg_api-authentication_basic' AND "type" = 'plugin';
