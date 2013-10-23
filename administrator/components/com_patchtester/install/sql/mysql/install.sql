CREATE TABLE IF NOT EXISTS `#__patchtester_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pull_id` int(11) NOT NULL,
  `data` longtext NOT NULL,
  `patched_by` int(11) NOT NULL,
  `applied` int(11) NOT NULL,
  `applied_version` varchar(25) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` varchar(3000) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
