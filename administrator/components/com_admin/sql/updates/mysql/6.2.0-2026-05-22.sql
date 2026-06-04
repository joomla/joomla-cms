--
-- Table structure for table `#__category_item_map`
--

CREATE TABLE IF NOT EXISTS `#__category_item_map` (
    `context` varchar(100) NOT NULL COMMENT 'Item type, e.g. com_content.article',
    `item_id` int unsigned NOT NULL COMMENT 'ID of the item, e.g. content.id, contact.id, etc.',
    `category_id` int NOT NULL COMMENT 'ID of the category from #__categories',
    `ordering` int NOT NULL DEFAULT 0 COMMENT 'Display order of categories',
    UNIQUE KEY `uc_context_item_category` (`context`, `item_id`, `category_id`),
    KEY `idx_context_category` (`context`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
