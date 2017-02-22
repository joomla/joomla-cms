ALTER TABLE "#__extensions" ADD COLUMN "package_id" bigint DEFAULT 0 NOT NULL;

UPDATE "#__extensions"
SET "package_id" = sub.extension_id
FROM (SELECT "extension_id" FROM "#__extensions" WHERE "type" = 'package' AND "element" = 'pkg_en-GB') AS sub
WHERE "type"= 'language' AND "element" = 'en-GB';
