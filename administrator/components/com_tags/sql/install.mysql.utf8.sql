CREATE TABLE IF NOT EXISTS `#__tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL default '0000-00-00 00:00:00',

  PRIMARY KEY (`id`),
  KEY `tag_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_path` (`path`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__tags`
--

INSERT INTO `#__tags` (`id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `images`, `urls`, `hits`, `language`, `version`)
VALUES (1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', '', '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', '', '',  0, '*', 1);


--
-- Table structure for table `#__content_types`
--

CREATE TABLE IF NOT EXISTS `#__content_types` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `table` varchar(255) NOT NULL DEFAULT '',
  `rules` text NOT NULL,
   `field_mappings` text NOT NULL,
   `router` varchar(255) NOT NULL  DEFAULT '',
  PRIMARY KEY (`type_id`),
  KEY `idx_alias` (`alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__content_types`
--

INSERT INTO `#__content_types` (`type_id`, `title`, `alias`, `table`, `rules`, `field_mappings`,`router`) VALUES
(0, 'Article', 'com_content.article', '#__content', '', '{"id":"id","title":"title","published":"state","alias":"alias","created_date":"created","modified_date":"modified","body":"introtext", "hits":"hits","publish_up":"publish_up","publish_down":"publish_down","access":"access"}','ContentHelperRoute::getArticleRoute'),
(0, 'Weblink', 'com_weblinks.weblink', '#__weblinks', '', '{"id":"id","title":"title","published":"state","alias":"alias","created_date":"created","modified_date":"modified","body":"description", "hits":"hits","publish_up":"publish_up","publish_down":"publish_down","access":"access"}','WeblinksHelperRoute::getWeblinkRoute'),
(0, 'Contact', 'com_contact.contact', '#__contact_details', '', '{"id":"id","title":"name","published":"published","alias":"alias","created_date":"created","modified_date":"modified","body":"address", "hits":"hits","publish_up":"publish_up","publish_down":"publish_down","access":"access"}','ContactHelperRoute::getContactRoute'),
(0, 'Newsfeed', 'com_newsfeeds.newsfeed', '#__newsfeeds', '', '{"id":"id","title":"name","published":"published","alias":"alias","created_date":"created","modified_date":"modified","body":"description", "hits":"hits","publish_up":"publish_up","publish_down":"publish_down","access":"access"}','NewsfeedsHelperRoute::getNewsfeedRoute'),
(0, 'User', 'com_users.user', '#__users', '', '{"id":"id","title":"name","published":"null","alias":"username","created_date":"registerdate","modified_date":"null","body":"null", "hits":"null","publish_up":"null","publish_down":"null","access":"null"}','UsersHelperRoute::getUserRoute'),
(0, 'Category', 'com_content.category', '#__categories', '', '{"id":"id","title":"title","published":"published","alias":"alias","created_date":"created_time","modified_date":"modified_time","body":"description", "hits":"hits","publish_up":"null","publish_down":"null","access":"access"}','ContentHelperRoute::getCategoryRoute'),
(0, 'Category', 'com_contact.category', '#__categories', '', '{"id":"id","title":"title","published":"published","alias":"alias","created_date":"created_time","modified_date":"modified_time","body":"description", "hits":"hits","publish_up":"null","publish_down":"null","access":"access"}','ContactHelperRoute::getCategoryRoute'),
(0, 'Category', 'com_newsfeeds.category', '#__categories', '', '{"id":"id","title":"title","published":"published","alias":"alias","created_date":"created_time","modified_date":"modified_time","body":"description", "hits":"hits","publish_up":"null","publish_down":"null","access":"access"}','NewsfeedsHelperRoute::getCategoryRoute'),
(0, 'Category', 'com_weblinks.category', '#__categories', '', '{"id":"id","title":"title","published":"published","alias":"alias","created_date":"created_time","modified_date":"modified_time","body":"description", "hits":"hits","publish_up":"null","publish_down":"null","access":"access"}','WeblinksHelperRoute::getCategoryRoute'),
(0, 'Tag', 'com_tags.tag', '#__tags', '', '{"id":"id","title":"title","published":"published","alias":"alias","created_date":"created_time","modified_date":"modified_time","body":"description", "hits":"hits","publish_up":"null","publish_down":"null","access":"access"}','TagsHelperRoute::getTagRoute');


CREATE TABLE IF NOT EXISTS `#__contentitem_tag_map` (
  `type_alias` varchar(255) NOT NULL DEFAULT '',
  `content_item_id` int(11) NOT NULL COMMENT 'PK from the content type table',
  `tag_id` int(11) NOT NULL COMMENT 'ID from the tag table',
  `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL DEFAULT '',
  `language` char(7) NOT NULL DEFAULT '',
 CONSTRAINT uc_ItemnameTagid UNIQUE (type_alias, content_item_id, tag_id),
 KEY idx_tag_name (tag_id, type_alias),
 KEY idx_date_id (tag_date, tag_id),
 KEY idx_language_id (language, tag_id),
 KEY idx_createddate_id (created_date, tag_id),
 KEY idx_title_id (title, tag_id),
 KEY idx_publish_id (publish_up, tag_id),
 KEY idx_modified_id (modified_date, tag_id)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maps items from content tables to tags';
