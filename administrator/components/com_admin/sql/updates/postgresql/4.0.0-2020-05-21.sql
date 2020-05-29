-- Renaming table
ALTER TABLE "#__ucm_history" RENAME TO "#__history";
-- Rename ucm_item_id to item_id as the new primary identifier for the original content item
ALTER TABLE "#__history" RENAME "ucm_item_id" TO "item_id";
ALTER TABLE "#__history" ALTER COLUMN "item_id" VARCHAR(50) NOT NULL DEFAULT "";
-- Extend the original field content with the alias of the content type
UPDATE "#__history" AS h INNER JOIN "#__content_types" AS c ON h.ucm_type_id = c.type_id SET h.item_id = CONCAT(c.type_alias, ".", h.item_id);
-- Now we don't need the ucm_type_id anymore and drop it.
ALTER TABLE "#__history" DROP COLUMN "ucm_type_id";
