--
-- Table structure for table `#__schemaorg`
--

CREATE TABLE IF NOT EXISTS `#__schemaorg` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int unsigned,
  `context` varchar(100),
  `schemaType` varchar(100),
  `schema` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Add plugins to `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`) VALUES
(0, 'plg_schemaorg_blogposting', 'plugin', 'blogposting', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 1, 0),
(0, 'plg_schemaorg_book', 'plugin', 'book', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 2, 0),
(0, 'plg_schemaorg_event', 'plugin', 'event', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 3, 0),
(0, 'plg_schemaorg_organization', 'plugin', 'organization', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 4, 0),
(0, 'plg_schemaorg_person', 'plugin', 'person', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 5, 0),
(0, 'plg_schemaorg_recipe', 'plugin', 'recipe', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 6, 0),
(0, 'plg_schemaorg_jobposting', 'plugin', 'jobposting', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 7, 0),
(0, 'plg_system_schemaorg', 'plugin', 'schemaorg', 'system', 0, 1, 1, 0, 0, '', '{}', '', 0, 0);
