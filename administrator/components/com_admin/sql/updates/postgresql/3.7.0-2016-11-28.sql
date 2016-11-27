-- Add core field to extensions table.
ALTER TABLE "#__extensions" ADD COLUMN "core" smallint DEFAULT 0 NOT NULL;

COMMENT ON COLUMN "#__extensions"."protected" IS 'Flag to indicate if the extension can be enabled/disabled.';
COMMENT ON COLUMN "#__extensions"."core" IS 'Flag to indicate if is a Joomla core extension. Core extensions can not be uninstalled.';

UPDATE "#__extensions"
SET "core" = 1
WHERE "element" IN ();
