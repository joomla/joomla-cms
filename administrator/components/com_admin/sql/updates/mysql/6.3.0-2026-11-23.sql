-- Ensure installations which applied the original mapping-table update use the covering reverse lookup index.
ALTER TABLE `#__category_item_map`
    DROP INDEX `idx_context_category`,
    ADD INDEX `idx_context_category` (`context`, `category_id`, `item_id`);
