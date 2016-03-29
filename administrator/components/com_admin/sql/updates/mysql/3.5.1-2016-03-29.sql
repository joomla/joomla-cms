--
-- Reset UTF-8 Multibyte (utf8mb4) or UTF-8 conversion status
-- to force a new conversion when updating from version 3.5.0
--

UPDATE `#__utf8_conversion` SET converted = 0 WHERE (SELECT COUNT(*) FROM `#__extensions` WHERE `extension_id`=700 AND `manifest_cache` LIKE '%"version":"3.5.0"%') = 1;
