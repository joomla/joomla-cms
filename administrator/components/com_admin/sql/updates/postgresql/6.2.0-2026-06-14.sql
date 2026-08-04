INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'mod_healthcheck', 'module', 'mod_healthcheck', '', 1, 1, 1, 0, 1, '', '{"context":"general"}', '', 0, 0)
ON CONFLICT DO NOTHING;

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_healthcheck_usermaintenance', 'plugin', 'usermaintenance', 'healthcheck', 0, 1, 1, 0, 1, '', '{"context":"general"}', '', 0, 0)
ON CONFLICT DO NOTHING;

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Health Checks', '', '', 1, 'cpanel-healthcheck', 0, NULL, NULL, NULL, 1, 'mod_healthcheck', 1, 1, '{"context":"general"}', 1, '*')
ON CONFLICT DO NOTHING;
