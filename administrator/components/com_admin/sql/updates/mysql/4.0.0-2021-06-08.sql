CREATE TABLE IF NOT EXISTS `#__cookiemanager_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(64) NOT NULL,
  `params` varchar(1024) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__cookiemanager_groups` (`title`) VALUES
('Cookie Consent Banner'),
('Cookie Settings Banner'),
('Cookie Consent Information');
