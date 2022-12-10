--
-- Table structure for table `#__schemaorg`
--

CREATE TABLE IF NOT EXISTS `#__schemaorg` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`itemId` int,
	`context` varchar(100),
	`schemaType` varchar(100),
	`schemaForm` text,
	`schema` text,
	PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;