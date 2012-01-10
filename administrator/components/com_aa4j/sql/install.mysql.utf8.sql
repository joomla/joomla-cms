-- $Id: install.mysql.utf8.sql 11/05/2011 18.33

DROP TABLE IF EXISTS `#__userextras`;
CREATE TABLE `#__userextras` (
  `id` int(11) NOT NULL,
  `nvisit` int(11) NOT NULL,
  `times` int(11) NOT NULL,
  `termid` int(11) NOT NULL,
  `pwsdexpires` date NOT NULL,
  `ip` char(20) NOT NULL,
  `citta` char(50) NOT NULL,
  `stato` char(3) NOT NULL,
  `countryname` varchar(50) NOT NULL,
  `latitude` varchar(25) NOT NULL,
  `longitude` varchar(25) NOT NULL,
  `acamp1` varchar(100) NOT NULL,
  `acamp2` varchar(100) NOT NULL,
  `acamp3` varchar(100) NOT NULL,
  `hscore` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
