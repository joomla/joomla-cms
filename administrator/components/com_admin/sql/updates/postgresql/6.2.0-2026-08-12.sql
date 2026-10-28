--
-- Table structure for table `#__category_item_map`
--

CREATE TABLE IF NOT EXISTS "#__category_item_map" (
    "context" varchar(100) NOT NULL,
    "item_id" integer NOT NULL,
    "category_id" integer NOT NULL,
    "ordering" integer DEFAULT 0 NOT NULL,
    PRIMARY KEY ("context", "item_id", "category_id")
);

CREATE INDEX "idx_context_category" ON "#__category_item_map" ("context", "category_id");

COMMENT ON COLUMN "#__category_item_map"."context" IS 'Item type, e.g. com_content.article';
COMMENT ON COLUMN "#__category_item_map"."item_id" IS 'ID of the item, e.g. content.id, contact.id, etc.';
COMMENT ON COLUMN "#__category_item_map"."category_id" IS 'ID of the category from #__categories';
COMMENT ON COLUMN "#__category_item_map"."ordering" IS 'Display order of categories';

-- Display additional category titles in content version history.
UPDATE "#__content_types"
SET "content_history_options" = jsonb_set(
    "content_history_options"::jsonb,
    '{displayLookup}',
    "content_history_options"::jsonb->'displayLookup' || jsonb_build_object(
        'sourceColumn', 'secondary_categories',
        'targetTable', '#__categories',
        'targetColumn', 'id',
        'displayColumn', 'title'
    )
)
WHERE "type_alias" IN (
    'com_banners.banner',
    'com_contact.contact',
    'com_content.article',
    'com_newsfeeds.newsfeed'
)
AND NOT EXISTS (
    SELECT * FROM jsonb_array_elements("content_history_options"::jsonb->'displayLookup')
    WHERE value = jsonb_build_object(
        'sourceColumn', 'secondary_categories',
        'targetTable', '#__categories',
        'targetColumn', 'id',
        'displayColumn', 'title'
    )
);
