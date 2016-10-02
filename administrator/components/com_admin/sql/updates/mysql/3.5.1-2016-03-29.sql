--
-- Reset UTF-8 Multibyte (utf8mb4) or UTF-8 conversion status
-- to force a new conversion when updating from version 3.5.0
--

UPDATE `#__utf8_conversion` SET `converted` = 0
 WHERE (SELECT COUNT(*) FROM `#__schemas` WHERE `extension_id`=700 AND `version_id` LIKE '3.5.0%') = 1;