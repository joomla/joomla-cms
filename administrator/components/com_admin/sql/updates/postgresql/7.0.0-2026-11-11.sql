--
-- Uninstall libraries/phpass
--
DELETE FROM "#__extensions"
 WHERE "type" = 'library' AND "element" = 'phpass';
