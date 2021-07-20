--
-- Table structure for table `#__cookiemanager_scripts`
--

CREATE TABLE IF NOT EXISTS `#__cookiemanager_scripts` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `position` int NOT NULL DEFAULT 4,
  `type` int NOT NULL DEFAULT 1,
  `code` text NOT NULL,
  `catid` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;
