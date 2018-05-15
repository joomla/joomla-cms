--
-- Table structure for table `#__workflows`
--

CREATE TABLE IF NOT EXISTS `#__workflows` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `extension` varchar(255) NOT NULL,
  `default` tinyint(1) NOT NULL  DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT NOW(),
  `created_by` int(10) NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL DEFAULT NOW(),
  `modified_by` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  KEY `title` (`title`(191)),
  KEY `extension` (`extension`(191)),
  KEY `default` (`default`),
  KEY `created` (`created`),
  KEY `created_by` (`created_by`),
  KEY `modified` (`modified`),
  KEY `modified_by` (`modified_by`)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflows`
--

INSERT INTO `#__workflows` (`id`, `asset_id`, `published`, `title`, `description`, `extension`, `default`, `ordering`, `created`, `created_by`, `modified`, `modified_by`) VALUES
(1, 0, 1, 'Joomla! Default', '', 'com_content', 1, 1, NOW(), 0, '0000-00-00 00:00:00', 0);

--
-- Table structure for table `#__workflow_associations`
--

CREATE TABLE IF NOT EXISTS `#__workflow_associations` (
  `item_id` int(10) NOT NULL DEFAULT 0 COMMENT 'Extension table id value',
  `state_id` int(10) NOT NULL COMMENT 'Foreign Key to #__workflow_states.id',
  `extension` varchar(100) NOT NULL,
  PRIMARY KEY (`item_id`, `state_id`, `extension`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_state_id` (`state_id`),
  KEY `idx_extension` (`extension`(100))
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__workflow_states`
--

CREATE TABLE IF NOT EXISTS `#__workflow_states` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `workflow_id` int(10) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `condition` enum('0','1','-2') NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `title` (`title`(191)),
  KEY `asset_id` (`asset_id`),
  KEY `default` (`default`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflow_states`
--

INSERT INTO `#__workflow_states` (`id`, `asset_id`, `ordering`, `workflow_id`, `published`, `title`, `description`, `condition`, `default`) VALUES
(1, 0, 1, 1, 1, 'Unpublished', '', '0', 0),
(2, 0, 2, 1, 1, 'Published', '', '1', 1),
(3, 0, 3, 1, 1, 'Trashed', '', '-2', 0),
(4, 0, 4, 1, 1, 'Archived', '', '1', 0);

--
-- Table structure for table `#__workflow_transitions`
--

CREATE TABLE IF NOT EXISTS `#__workflow_transitions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `workflow_id` int(10) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `from_state_id` int(10) NOT NULL,
  `to_state_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`(191)),
  KEY `asset_id` (`asset_id`),
  KEY `from_state_id` (`from_state_id`),
  KEY `to_state_id` (`to_state_id`),
  KEY `workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflow_transitions`
--

INSERT INTO `#__workflow_transitions` (`id`, `asset_id`, `published`, `ordering`, `workflow_id`, `title`, `description`, `from_state_id`, `to_state_id`) VALUES
(1, 0, 1, 1, 1, 'Unpublish', '', -1, 1),
(2, 0, 1, 2, 1, 'Publish', '', -1, 2),
(3, 0, 1, 3, 1, 'Trash', '', -1, 3),
(4, 0, 1, 4, 1, 'Archive', '', -1, 4);

--
-- Creating Associations for existing content
--
INSERT INTO `#__workflow_associations` (`item_id`, `state_id`, `extension`)
SELECT `id`, CASE WHEN `state` = -2 THEN 3 WHEN `state` = 0 THEN 1 WHEN `state` = 2 THEN 4 ELSE 2 END, 'com_content' FROM `#__content`;
