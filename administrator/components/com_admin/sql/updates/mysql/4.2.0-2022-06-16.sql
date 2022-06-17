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
  `overlay` tinyint NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL,
  `checked_out` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
  
--
-- Table structure for table `#__guidedtour_steps`
--

CREATE TABLE IF NOT EXISTS `#__guidedtour_steps` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
  KEY `idx_tour` (`tour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;