CREATE TABLE IF NOT EXISTS `#__tag_content` (
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type_alias` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'FK to the content types table',
  `title` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `metadata` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Most recent user that modified',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `urls` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadesc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`,`type_alias`),
  KEY `idx_access` (`access`),
  KEY `idx_alias` (`alias`(100)),
  KEY `idx_language` (`language`),
  KEY `idx_title` (`title`(100)),
  KEY `idx_modified_time` (`modified_time`),
  KEY `idx_created_time` (`created_time`),
  KEY `idx_content_type` (`type_alias`),
  KEY `idx_core_modified_user_id` (`modified_user_id`),
  KEY `idx_core_created_user_id` (`created_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO #__tag_content (`content_id`, `type_alias`, `title`, `alias`, `body`, `state`, `access`, `params`, `featured`, `metadata`,
  `created_user_id`, `created_by_alias`, `created_time`, `modified_user_id`, `modified_time`, `language`, `publish_up`, `publish_down`,
  `images`, `urls`, `hits`, `ordering`, `metakey`, `metadesc`, `catid`)
   SELECT t.core_content_item_id, t.core_type_alias, t.core_title, t.core_alias, t.core_body, t.core_state, t.core_access, t.core_params, t.core_featured, t.core_metadata,
        t.core_created_user_id, t.core_created_by_alias, t.core_created_time, t.core_modified_user_id, t.core_modified_time, t.core_language, t.core_publish_up, t.core_publish_down,
        t.core_images, t.core_urls, t.core_hits, t.core_ordering, t.core_metakey, t.core_metadesc, t.core_catid
        FROM #__ucm_content t;

CREATE TABLE IF NOT EXISTS `#__tag_content_map` (
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type_alias` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tagged_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_id`,`content_id`,`type_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO #__tag_content_map (`tag_id`, `type_alias`, `content_id`, `tagged_on`) SELECT t.tag_id, t.type_alias, t.content_item_id, t.tag_date FROM #__contentitem_tag_map t;
