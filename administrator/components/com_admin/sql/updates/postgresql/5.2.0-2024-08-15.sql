--
-- Add mod_redirect module to `#__extensions`
-- and create an entry in `#__modules` and `#__modules_menu`
--

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'mod_redirect', 'module', 'mod_redirect', '', 1, 1, 0, 0, 1, '{}', '{}', '', 0, 0);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Redirects: Links', '', '', 1, 'cpanel', NULL, NULL, '2024-08-15 14:46:26', NULL, 0, 'mod_redirect', 3, 1, '{"state":"0","http_status":"301","ordering":"a.hits DESC","count":5,"layout":"_:default","moduleclass_sfx":"","automatic_title":0,"cache":1,"cache_time":900,"cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*');
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);
