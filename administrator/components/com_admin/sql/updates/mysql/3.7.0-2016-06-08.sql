CREATE TABLE IF NOT EXISTS `#__nullDate_conversion` (
  `converted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__nullDate_conversion` (`converted`) VALUES ('0000-00-00 00:00:00');


ALTER TABLE `#__menu` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';