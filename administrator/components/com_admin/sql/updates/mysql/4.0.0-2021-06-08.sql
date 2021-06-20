CREATE TABLE IF NOT EXISTS `#__cookiemanager_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(64) NOT NULL,
  `params` varchar(1024) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;

INSERT INTO `#__cookiemanager_groups` (`id`, `title`, `params`) VALUES
(1, 'Cookie Consent Banner', '{}'),
(2, 'Cookie Settings Banner', '{}'),
(3, 'Cookie Consent Information', '{}');
