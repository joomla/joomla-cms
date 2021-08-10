--
-- Table structure for table `#__cookiemanager_consents`
--

CREATE TABLE IF NOT EXISTS `#__cookiemanager_consents` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uuid` varchar(100) NOT NULL,
  `ccuuid` varchar(100) NOT NULL,
  `consent` varchar(255) NOT NULL,
  `consent_date` datetime NOT NULL,
  `user_agent` varchar(150) NOT NULL,
  `url` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;
