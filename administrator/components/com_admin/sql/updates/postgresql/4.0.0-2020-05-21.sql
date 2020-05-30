-- Renaming table
ALTER TABLE "#__ucm_history" RENAME TO "#__history";
-- Rename ucm_item_id to item_id as the new primary identifier for the original content item
ALTER TABLE "#__history" RENAME "ucm_item_id" TO "item_id";
ALTER TABLE "#__history" ALTER COLUMN "item_id" TYPE character varying(50);
ALTER TABLE "#__history" ALTER COLUMN "item_id" SET NOT NULL;
ALTER TABLE "#__history" ALTER COLUMN "item_id" DROP DEFAULT;

-- Extend the original field content with the alias of the content type
UPDATE "#__history" AS h SET "item_id" = CONCAT(c."type_alias", '.', "item_id") FROM "#__content_types" AS c WHERE h."ucm_type_id" = c."type_id";

-- Now we don't need the ucm_type_id anymore and drop it.
ALTER TABLE "#__history" DROP COLUMN "ucm_type_id";
ALTER TABLE "#__history" ALTER COLUMN "save_date" DROP DEFAULT;
