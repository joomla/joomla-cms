INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state")
SELECT "extension_id", 'English (en-GB)', 'language', 'en-GB', '', 3, 1, 1, 1, '', '', 0, NULL, 0, 0 FROM "#__extensions" WHERE "name" = 'English (en-GB) Language Pack';
