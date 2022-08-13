--
-- Table structure for table `#__guidedtours`
--

CREATE TABLE IF NOT EXISTS `#__guidedtours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ordering` int NOT NULL DEFAULT 0,
  `extensions` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL,
  `checked_out` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__guidedtours`
--

INSERT IGNORE INTO `#__guidedtours` (`id`, `asset_id`, `title`, `description`, `ordering`, `extensions`, `url`, `created`, `created_by`, `modified`, `modified_by`, `checked_out_time`, `checked_out`, `published`) VALUES
(1, 0, 'How to create a Guided Tour in Joomla Backend?', '<p>This Tour will show you how you can create a Guided Tour in the Joomla Backend!</p>', 0, '[\"com_guidedtours\"]', 'administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, NULL, 0, 1);

-- --------------------------------------------------------
  
--
-- Table structure for table `#__guidedtour_steps`
--

CREATE TABLE IF NOT EXISTS `#__guidedtour_steps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tour_id` int NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `published` tinyint NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `ordering` int NOT NULL DEFAULT 0,
  `step-no` int NOT NULL DEFAULT 0,
  `position` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tour` (`tour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__guidedtour_steps`
--

INSERT IGNORE INTO `#__guidedtour_steps` (`id`, `tour_id`, `title`, `published`, `description`, `ordering`, `step-no`, `position`, `target`, `url`, `created`, `created_by`, `modified`, `modified_by`) VALUES
(1, 1, 'Click here!', 1, '<p>This Tour will show you how you can create a Guided Tour in the Joomla Backend!</p>', 0, 1, 'bottom', '.button-new','administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(2, 1, 'Add title for your Tour', 1, '<p>Here you have to add the title of your Tour Step. </p>', 0, 1, 'bottom', '#jform_title', 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(3, 1, 'Add Content', 1, '<p>Add the content of your Tour here!</p>', 0, 1, 'bottom', '#details','administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(4, 1, 'Plugin selector', 1, '<p>Select the extensions where you want to show your Tour. e.g If you are creating a tour which is only in \'Users\' extensions then select Users here.</p>', 0, 1, 'bottom', '.choices__inner', 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(5, 1, 'URL', 1, '<p>Add Relative URL of the page from where you want to start your Tour. </p>', 0, 1, 'bottom', '#jform_url', 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(6, 1, 'Save and Close', 1, '<p>Save and close the tour</p>', 0, 1, 'bottom', '#save-group-children-save','administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(7, 1, 'Create steps for your Tour', 1, '<p>Click on steps icon in the right</p>', 0, 1, 'right', '.btn-info','administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(8, 1, 'Click here!', 1, '<p>Click here to create a new Step for your Tour</p>', 0, 1, 'bottom', '.button-new', 'administrator/index.php?option=com_guidedtours&view=steps&tour_id=1', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(9, 1, 'Add title for your Tour.', 1, '<p>Here you have to add the title of your Tour Step. </p>', 0, 1, 'bottom', '#jform_title','administrator/index.php?option=com_guidedtours&view=step&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(10, 1, 'Add Content', 1, '<p>Add the content of your Tour here!</p>', 0, 1, 'bottom', '#details', 'administrator/index.php?option=com_guidedtours&view=step&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(11, 1, 'Position ', 1, '<p>Add the position of the Step you want. e.g. Right, Left, Top, Bottom.</p>', 0, 1, 'bottom', '#jform_position','administrator/index.php?option=com_guidedtours&view=step&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(12, 1, 'Target', 1, '<p>Add the ID name or Class name of the element where you want to attach your Tour.</p>', 0, 1, 'bottom', '#jform_target', 'administrator/index.php?option=com_guidedtours&view=step&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(13, 1, 'Multi-page URL', 1, '<p>Add Relative URL of the page from where next step starts</p>', 0, 1, 'bottom', '#jform_url','administrator/index.php?option=com_guidedtours&view=step&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(14, 1, 'Save and Close', 1, '<p>Save and close the step</p>', 0, 1, 'bottom', '#save-group-children-save', 'administrator/index.php?option=com_guidedtours&view=step&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0),
(15, 1, 'Congratulations!!!', 1, '<p>You successfully created your first Guided Tour!</p>', 0, 1, 'bottom', '', 'administrator/index.php?option=com_guidedtours&view=step&layout=edit', CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0);

-- Add new `#__extensions`
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`) VALUES
(0, 'com_guidedtours', 'component', 'com_guidedtours', '', 1, 1, 0, 0, 1, '', '{}', '', 0, 0),
(0, 'plg_system_tour', 'plugin', 'tour', 'system', 0, 1, 1, 0, 0, '', '{}', '', 0, 0);
