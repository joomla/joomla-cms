INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'mod_backward', 'module', 'mod_backward', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" e WHERE e."type" = 'module' AND e."element" = 'mod_backward' AND e."name" = 'mod_backward' AND e."client_id" = 1);

INSERT INTO "#__modules" ("title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language")
SELECT 'Backward Compatibility', '', '', 1, 'status', 0, NULL, NULL, NULL, 1, 'mod_backward', 1, 1, '', 1, '*'
WHERE NOT EXISTS (SELECT * FROM "#__modules" m WHERE m."title" = 'Backward Compatibility' AND m."position" = 'status' AND m."module" = 'mod_backward' AND m."client_id" = 1);

INSERT INTO "#__modules_menu" ("moduleid", "menuid") 
SELECT currval(pg_get_serial_sequence('#__modules','id')), 0
WHERE NOT EXISTS (
    SELECT 1 FROM "#__modules_menu" 
    WHERE "moduleid" = currval(pg_get_serial_sequence('#__modules','id')) AND "menuid" = 0
);

