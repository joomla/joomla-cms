--
-- Table structure for table `#__csp`
--

CREATE TABLE IF NOT EXISTS `#__csp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_uri` varchar(500) NOT NULL DEFAULT '',
  `blocked_uri` varchar(500) NOT NULL DEFAULT '',
  `directive` varchar(500) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;