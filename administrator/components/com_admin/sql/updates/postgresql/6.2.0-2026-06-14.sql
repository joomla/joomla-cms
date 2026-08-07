INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'mod_healthcheck', 'module', 'mod_healthcheck', '', 1, 1, 1, 0, 1, '', '{"context":"general"}', '', 0, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" g WHERE g."element" = 'mod_healthcheck' AND g."client_id" = 1);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'plg_healthcheck_usermaintenance', 'plugin', 'usermaintenance', 'healthcheck', 0, 1, 1, 0, 1, '', '{"context":"general"}', '', 0, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" g WHERE g."element" = 'usermaintenance' AND g."folder" = 'healthcheck');
