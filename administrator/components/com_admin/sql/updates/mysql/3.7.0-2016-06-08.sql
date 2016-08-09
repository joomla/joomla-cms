--
-- Table structure for table `#__nullDate_conversion`
--

CREATE TABLE IF NOT EXISTS `#__nullDate_conversion` (
  `converted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__nullDate_conversion`
--

INSERT INTO `#__nullDate_conversion` (`converted`) VALUES ('0000-00-00 00:00:00');