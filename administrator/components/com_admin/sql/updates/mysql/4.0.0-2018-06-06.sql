CREATE TABLE IF NOT EXISTS `#__finder_logging` (
  `searchterm` VARCHAR(255) NOT NULL DEFAULT '',
  `md5sum` VARCHAR(32) NOT NULL DEFAULT '',
  `query` BLOB NOT NULL,
  `hits` INT(11) NOT NULL DEFAULT '1',
  `results` INT(11) NOT NULL DEFAULT '0',
  UNIQUE INDEX `md5sum` (`md5sum`),
  INDEX `searchterm` (`searchterm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;
