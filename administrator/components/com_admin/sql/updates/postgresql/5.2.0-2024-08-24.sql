-- Add new "#__extensions"
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data") VALUES
(0, 'mod_community_info', 'module', 'mod_community_info', '', 1, 1, 1, 0, 1, '', '{}', '');

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
('Joomla! Community and News', '', '', 1, 'cpanel', NULL, NULL, 1, 'mod_community_info', 1, 1, '{"endpoint":"https://test.joomla.spuur.ch/joomla-community-api/links.php","fallback-location":"en-GB","location":"","location_name":"","auto_location":"1","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*');

UPDATE "#__modules" SET "ordering" = 7 WHERE "module" = 'mod_popular';
UPDATE "#__modules" SET "ordering" = 5 WHERE "module" = 'mod_latest';
UPDATE "#__modules" SET "ordering" = 3 WHERE "module" = 'mod_logged';
UPDATE "#__modules" SET "ordering" = 2 WHERE "module" = 'mod_sampledata';
UPDATE "#__modules" SET "ordering" = 4 WHERE "module" = 'mod_latestactions';
UPDATE "#__modules" SET "ordering" = 6 WHERE "module" = 'mod_privacy_dashboard';

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES (currval(pg_get_serial_sequence('#__modules','id')), 0);
