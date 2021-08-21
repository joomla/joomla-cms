--
-- Table structure for table `#__workflows`
--

CREATE TABLE IF NOT EXISTS `#__workflows` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `extension` varchar(50) NOT NULL,
  `default` tinyint NOT NULL  DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `checked_out` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_extension` (`extension`),
  KEY `idx_default` (`default`),
  KEY `idx_created` (`created`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_modified` (`modified`),
  KEY `idx_modified_by` (`modified_by`),
  KEY `idx_checked_out` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflows`
--

INSERT INTO `#__workflows` (`id`, `asset_id`, `published`, `title`, `description`, `extension`, `default`, `ordering`, `created`, `created_by`, `modified`, `modified_by`, `checked_out_time`, `checked_out`) VALUES
(1, 0, 1, 'COM_WORKFLOW_BASIC_WORKFLOW', '', 'com_content.article', 1, 1, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, NULL, 0);

--
-- Table structure for table `#__workflow_associations`
--

CREATE TABLE IF NOT EXISTS `#__workflow_associations` (
  `item_id` int NOT NULL DEFAULT 0 COMMENT 'Extension table id value',
  `stage_id` int NOT NULL COMMENT 'Foreign Key to #__workflow_stages.id',
  `extension` varchar(50) NOT NULL,
  PRIMARY KEY (`item_id`, `extension`),
  KEY `idx_item_stage_extension` (`item_id`, `stage_id`, `extension`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_stage_id` (`stage_id`),
  KEY `idx_extension` (`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__workflow_stages`
--

CREATE TABLE IF NOT EXISTS `#__workflow_stages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `workflow_id` int NOT NULL,
  `published` tinyint NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `default` tinyint NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `checked_out` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_id` (`workflow_id`),
  KEY `idx_checked_out` (`checked_out`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_default` (`default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflow_stages`
--

INSERT INTO `#__workflow_stages` (`id`, `asset_id`, `ordering`, `workflow_id`, `published`, `title`, `description`, `default`, `checked_out_time`, `checked_out`) VALUES
(1, 0, 1, 1, 1, 'COM_WORKFLOW_BASIC_STAGE', '', 1, NULL, 0);

--
-- Table structure for table `#__workflow_transitions`
--

CREATE TABLE IF NOT EXISTS `#__workflow_transitions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `workflow_id` int NOT NULL,
  `published` tinyint NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `from_stage_id` int NOT NULL,
  `to_stage_id` int NOT NULL,
  `options` text NOT NULL,
  `checked_out_time` datetime,
  `checked_out` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_checked_out` (`checked_out`),
  KEY `idx_from_stage_id` (`from_stage_id`),
  KEY `idx_to_stage_id` (`to_stage_id`),
  KEY `idx_workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflow_transitions`
--

INSERT INTO `#__workflow_transitions` (`id`, `asset_id`, `published`, `ordering`, `workflow_id`, `title`, `description`, `from_stage_id`, `to_stage_id`, `options`, `checked_out_time`, `checked_out`) VALUES
(1, 0, 1, 1, 1, 'Unpublish', '', -1, 1, '{"publishing":"0"}', NULL, 0),
(2, 0, 1, 2, 1, 'Publish', '', -1, 1, '{"publishing":"1"}', NULL, 0),
(3, 0, 1, 3, 1, 'Trash', '', -1, 1, '{"publishing":"-2"}', NULL, 0),
(4, 0, 1, 4, 1, 'Archive', '', -1, 1, '{"publishing":"2"}', NULL, 0),
(5, 0, 1, 5, 1, 'Feature', '', -1, 1, '{"featuring":"1"}', NULL, 0),
(6, 0, 1, 6, 1, 'Unfeature', '', -1, 1, '{"featuring":"0"}', NULL, 0),
(7, 0, 1, 7, 1, 'Publish & Feature', '', -1, 1, '{"publishing":"1","featuring":"1"}', NULL, 0);

--
-- Creating extension entry
--
-- Note that the old pseudo null dates have to be used for the `checked_out_time`
-- column because the conversion to real null dates will be done with a later
-- update SQL script.
--

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(0, 'com_workflow', 'component', 'com_workflow', '', 1, 1, 0, 1, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_workflow_publishing', 'plugin', 'publishing', 'workflow', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_workflow_featuring', 'plugin', 'featuring', 'workflow', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0),
(0, 'plg_workflow_notification', 'plugin', 'notification', 'workflow', 0, 1, 1, 0, '', '{}', '', 0, '0000-00-00 00:00:00', 0, 0);

--
-- Creating Associations for existing content
--
INSERT INTO `#__workflow_associations` (`item_id`, `stage_id`, `extension`)
SELECT `id`, 1, 'com_content.article' FROM `#__content`;
