INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 'plg_captcha_powcaptcha', 'plugin', 'powcaptcha', 'captcha', 0, 1, 1, 0, 1, '', '{}', '', 0, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" e WHERE e."type" = 'plugin' AND e."element" = 'powcaptcha' AND e."folder" = 'captcha' AND e."client_id" = 0);

