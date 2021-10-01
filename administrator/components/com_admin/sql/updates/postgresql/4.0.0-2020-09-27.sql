-- From 4.0.0-2020-09-19.sql
UPDATE "#__menu" SET "link"='index.php?option=com_finder' WHERE "menutype"='main' AND "title"='com_finder' AND "link"='index.php?option=com_finder&view=index';

-- From 4.0.0-2020-09-22.sql
UPDATE "#__menu" SET "link"='index.php?option=com_tags&view=tags' WHERE "menutype"='main' AND "path"='Tags';
UPDATE "#__menu" SET "link"='index.php?option=com_associations&view=associations' WHERE "menutype"='main' AND "path"='Multilingual Associations';

-- From 4.0.0-2020-09-27.sql
DELETE FROM "#__extensions" WHERE "name" = 'plg_content_imagelazyload' AND "type" = 'plugin' AND "element" = 'imagelazyload' AND "folder" = 'content' AND "client_id" = 0;
DELETE FROM "#__extensions" WHERE "name" = 'plg_authentication_gmail' AND "type" = 'plugin' AND "element" = 'gmail' AND "folder" = 'authentication' AND "client_id" = 0;

--
-- Delete possibly duplicate record for plg_sampledata_multilang
--
DELETE FROM "#__extensions"
 WHERE "name" = 'plg_sampledata_multilang' AND "type" = 'plugin' AND "element" = 'multilang' AND "folder" = 'sampledata' AND "client_id" = 0
   AND "extension_id" < (SELECT MAX("extension_id") FROM "#__extensions" WHERE "name" = 'plg_sampledata_multilang' AND "type" = 'plugin' AND "element" = 'multilang' AND "folder" = 'sampledata' AND "client_id" = 0);

--
-- Enable the remaining plg_sampledata_multilang record in case it has been disabled before
--
UPDATE "#__extensions" SET "enabled" = 1 WHERE "name" = 'plg_sampledata_multilang' AND "type" = 'plugin' AND "element" = 'multilang' AND "folder" = 'sampledata' AND "client_id" = 0;
