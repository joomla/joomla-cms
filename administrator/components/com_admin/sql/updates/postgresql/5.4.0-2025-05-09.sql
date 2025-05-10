
-- Alpha repository for automated update testing
UPDATE "#__update_sites"
SET "type" = 'tuf', "location" = 'https://update.joomla.org/alpha/'
WHERE "update_site_id" IN (SELECT ue."update_site_id" FROM "#__update_sites_extensions" AS ue JOIN "#__extensions" AS e ON (e."extension_id" = ue."extension_id") WHERE e."type"='file' AND e."element"='joomla');

