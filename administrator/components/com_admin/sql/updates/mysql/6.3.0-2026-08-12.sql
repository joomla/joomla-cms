--
-- Table structure for table `#__category_item_map`
--

CREATE TABLE IF NOT EXISTS `#__category_item_map` (
    `context` varchar(100) NOT NULL COMMENT 'Item type, e.g. com_content.article',
    `item_id` int unsigned NOT NULL COMMENT 'ID of the item, e.g. content.id, contact.id, etc.',
    `category_id` int NOT NULL COMMENT 'ID of the category from #__categories',
    `ordering` int NOT NULL DEFAULT 0 COMMENT 'Display order of categories',
    PRIMARY KEY (`context`, `item_id`, `category_id`),
    KEY `idx_context_category` (`context`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Display additional category titles in content version history.
UPDATE `#__content_types`
SET `content_history_options` = JSON_ARRAY_APPEND(
    `content_history_options`,
    '$.displayLookup',
    JSON_OBJECT(
        'sourceColumn', 'secondary_categories',
        'targetTable', '#__categories',
        'targetColumn', 'id',
        'displayColumn', 'title'
    )
)
WHERE `type_alias` IN (
    'com_banners.banner',
    'com_contact.contact',
    'com_content.article',
    'com_newsfeeds.newsfeed'
)
AND NOT JSON_CONTAINS(
    JSON_EXTRACT(`content_history_options`, '$.displayLookup'),
    JSON_OBJECT(
        'sourceColumn', 'secondary_categories',
        'targetTable', '#__categories',
        'targetColumn', 'id',
        'displayColumn', 'title'
    )
);
