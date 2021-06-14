CREATE TABLE IF NOT EXISTS `#__cookiemanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__cookiemanager` (`title`) VALUES
('Cookie Consent Banner'),
('Cookie Settings Banner'),
('Cookie Consent Information');