-- Com_csp has been removed from the final build.
-- But here it still needs to create the table so it can be later deleted on update with script
-- 4.0.0-2021-05-04.sql regardles from which Joomla version an update is made.

CREATE TABLE IF NOT EXISTS `#__csp` (
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
