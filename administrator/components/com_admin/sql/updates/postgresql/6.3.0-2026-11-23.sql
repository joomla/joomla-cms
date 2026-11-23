-- Ensure installations which applied the original mapping-table update use the covering reverse lookup index.
DROP INDEX IF EXISTS "idx_context_category";
CREATE INDEX "idx_context_category" ON "#__category_item_map" ("context", "category_id", "item_id");
