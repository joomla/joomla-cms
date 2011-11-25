ALTER TABLE `#__newsfeeds` CHANGE `alias` `alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';
ALTER TABLE `#__content` CHANGE `alias` `alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '';
ALTER TABLE `#__content` CHANGE `title_alias` `title_alias` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Deprecated in Joomla! 3.0';

