/* Changes to tables where data type conflicts exist with MySQL (mainly dealing with null values */
ALTER TABLE "#__modules" ALTER COLUMN "content" SET DEFAULT '';
ALTER TABLE "#__updates" ALTER COLUMN "data" SET DEFAULT '';

/* Tags database schema */

--
-- Table: #__content_types
--
CREATE TABLE "#__content_types" (
  "type_id" serial NOT NULL,
  "type_title" character varying(255) NOT NULL DEFAULT '',
  "type_alias" character varying(255) NOT NULL DEFAULT '',
  "table" character varying(255) NOT NULL DEFAULT '',
  "rules" text NOT NULL,
  "field_mappings" text NOT NULL,
  "router" character varying(255) NOT NULL DEFAULT '',
  PRIMARY KEY ("type_id")
);
CREATE INDEX "#__content_types_idx_alias" ON "#__content_types" ("type_alias");

--
-- Dumping data for table #__content_types
--
INSERT INTO "#__content_types" ("type_id", "type_title", "type_alias", "table", "rules", "field_mappings", "router") VALUES
(1, 'Article', 'com_content.article', '{"special":{"dbtable":"#__content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}], "special": [{"fulltext":"fulltext"}]}','ContentHelperRoute::getArticleRoute'),
(2, 'Contact', 'com_contact.contact', '{"special":{"dbtable":"#__contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}], "special": [{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}]}','ContactHelperRoute::getContactRoute'),
(3, 'Newsfeed', 'com_newsfeeds.newsfeed', '{"special":{"dbtable":"#__newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}], "special": [{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}]}','NewsfeedsHelperRoute::getNewsfeedRoute'),
(4, 'User', 'com_users.user', '{"special":{"dbtable":"#__users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}], "special": [{}]}','UsersHelperRoute::getUserRoute'),
(5, 'Article Category', 'com_content.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','ContentHelperRoute::getCategoryRoute'),
(6, 'Contact Category', 'com_contact.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','ContactHelperRoute::getCategoryRoute'),
(7, 'Newsfeeds Category', 'com_newsfeeds.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','NewsfeedsHelperRoute::getCategoryRoute'),
(8, 'Tag', 'com_tags.tag', '{"special":{"dbtable":"#__tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}]}','TagsHelperRoute::getTagRoute');

SELECT nextval('#__content_types_type_id_seq');
SELECT setval('#__content_types_type_id_seq', 10000, false);

--
-- Table: #__contentitem_tag_map
--
CREATE TABLE "#__contentitem_tag_map" (
  "type_alias" character varying(255) NOT NULL DEFAULT '',
  "core_content_id" integer NOT NULL,
  "content_item_id" integer NOT NULL,
  "tag_id" integer NOT NULL,
  "tag_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
 CONSTRAINT "uc_ItemnameTagid" UNIQUE ("type_alias", "content_item_id", "tag_id")
);

CREATE INDEX "#__contentitem_tag_map_idx_tag_type" ON "#__contentitem_tag_map" ("tag_id", "type_alias");
CREATE INDEX "#__contentitem_tag_map_idx_date_id" ON "#__contentitem_tag_map" ("tag_date", "tag_id");
CREATE INDEX "#__contentitem_tag_map_idx_tag" ON "#__contentitem_tag_map" ("tag_id");
CREATE INDEX "#__contentitem_tag_map_idx_core_content_id" ON "#__contentitem_tag_map" ("core_content_id");

COMMENT ON COLUMN "#__contentitem_tag_map"."core_content_id" IS 'PK from the core content table';
COMMENT ON COLUMN "#__contentitem_tag_map"."content_item_id" IS 'PK from the content type table';
COMMENT ON COLUMN "#__contentitem_tag_map"."tag_id" IS 'PK from the tag table';
COMMENT ON COLUMN "#__contentitem_tag_map"."tag_date" IS 'Date of most recent save for this tag-item';

-- --------------------------------------------------------

--
-- Table: #__tags
--
CREATE TABLE "#__tags" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" character varying(255) DEFAULT '' NOT NULL,
  "title" character varying(255) NOT NULL,
  "alias" character varying(255) DEFAULT '' NOT NULL,
  "note" character varying(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "metadesc" character varying(1024) NOT NULL,
  "metakey" character varying(1024) NOT NULL,
  "metadata" character varying(2048) NOT NULL,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by_alias" character varying(255) DEFAULT '' NOT NULL,
  "modified_user_id" integer DEFAULT 0 NOT NULL,
  "modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "images" text NOT NULL,
  "urls" text NOT NULL,
  "hits" integer DEFAULT 0 NOT NULL,
  "language" character varying(7) DEFAULT '' NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__tags_cat_idx" ON "#__tags" ("published", "access");
CREATE INDEX "#__tags_idx_access" ON "#__tags" ("access");
CREATE INDEX "#__tags_idx_checkout" ON "#__tags" ("checked_out");
CREATE INDEX "#__tags_idx_path" ON "#__tags" ("path");
CREATE INDEX "#__tags_idx_left_right" ON "#__tags" ("lft", "rgt");
CREATE INDEX "#__tags_idx_alias" ON "#__tags" ("alias");
CREATE INDEX "#__tags_idx_language" ON "#__tags" ("language");

--
-- Dumping data for table #__tags
--

INSERT INTO "#__tags" ("id", "parent_id", "lft", "rgt", "level", "path", "title", "alias", "note", "description", "published", "checked_out", "checked_out_time", "access", "params", "metadesc", "metakey", "metadata", "created_user_id", "created_time", "created_by_alias", "modified_user_id", "modified_time", "images", "urls", "hits", "language", "version") VALUES
(1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 0, '1970-01-01 00:00:00', 1, '{}', '', '', '', 42, '1970-01-01 00:00:00', '', 0, '1970-01-01 00:00:00', '', '',  0, '*', 1);

SELECT nextval('#__tags_id_seq');
SELECT setval('#__tags_id_seq', 2, false);

--
-- Table: #__ucm_base
--
CREATE TABLE "#__ucm_base" (
  "ucm_id" serial NOT NULL,
  "ucm_item_id" bigint NOT NULL,
  "ucm_type_id" bigint NOT NULL,
  "ucm_language_id" bigint NOT NULL,
  PRIMARY KEY ("ucm_id")
);
CREATE INDEX "#__ucm_base_ucm_item_id" ON "#__ucm_base" ("ucm_item_id");
CREATE INDEX "#__ucm_base_ucm_type_id" ON "#__ucm_base" ("ucm_type_id");
CREATE INDEX "#__ucm_base_ucm_language_id" ON "#__ucm_base" ("ucm_language_id");

--
-- Table: #__ucm_content
--
CREATE TABLE "#__ucm_content" (
  "core_content_id" serial NOT NULL,
  "core_type_alias" character varying(255) DEFAULT '' NOT NULL,
  "core_title" character varying(255) NOT NULL,
  "core_alias" character varying(255) DEFAULT '' NOT NULL,
  "core_body" text NOT NULL,
  "core_state" smallint DEFAULT 0 NOT NULL,
  "core_checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_checked_out_user_id" bigint DEFAULT 0 NOT NULL,
  "core_access" bigint DEFAULT 0 NOT NULL,
  "core_params" text NOT NULL,
  "core_featured" smallint DEFAULT 0 NOT NULL,
  "core_metadata" text NOT NULL,
  "core_created_user_id" bigint DEFAULT 0 NOT NULL,
  "core_created_by_alias" character varying(255) DEFAULT '' NOT NULL,
  "core_created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_modified_user_id" bigint DEFAULT 0 NOT NULL,
  "core_modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_language" character varying(7) DEFAULT '' NOT NULL,
  "core_publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_content_item_id" bigint DEFAULT 0 NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "core_images" text NOT NULL,
  "core_urls" text NOT NULL,
  "core_hits" bigint DEFAULT 0 NOT NULL,
  "core_version" bigint DEFAULT 1 NOT NULL,
  "core_ordering" bigint DEFAULT 0 NOT NULL,
  "core_metakey" text NOT NULL,
  "core_metadesc" text NOT NULL,
  "core_catid" bigint DEFAULT 0 NOT NULL,
  "core_xreference" character varying(50) DEFAULT '' NOT NULL,
  "core_type_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("core_content_id"),
  CONSTRAINT "#__ucm_content_idx_type_alias_item_id" UNIQUE ("core_type_alias", "core_content_item_id")
);
CREATE INDEX "#__ucm_content_tag_idx" ON "#__ucm_content" ("core_state", "core_access");
CREATE INDEX "#__ucm_content_idx_access" ON "#__ucm_content" ("core_access");
CREATE INDEX "#__ucm_content_idx_alias" ON "#__ucm_content" ("core_alias");
CREATE INDEX "#__ucm_content_idx_language" ON "#__ucm_content" ("core_language");
CREATE INDEX "#__ucm_content_idx_title" ON "#__ucm_content" ("core_title");
CREATE INDEX "#__ucm_content_idx_modified_time" ON "#__ucm_content" ("core_modified_time");
CREATE INDEX "#__ucm_content_idx_created_time" ON "#__ucm_content" ("core_created_time");
CREATE INDEX "#__ucm_content_idx_content_type" ON "#__ucm_content" ("core_type_alias");
CREATE INDEX "#__ucm_content_idx_core_modified_user_id" ON "#__ucm_content" ("core_modified_user_id");
CREATE INDEX "#__ucm_content_idx_core_checked_out_user_id" ON "#__ucm_content" ("core_checked_out_user_id");
CREATE INDEX "#__ucm_content_idx_core_created_user_id" ON "#__ucm_content" ("core_created_user_id");
CREATE INDEX "#__ucm_content_idx_core_type_id" ON "#__ucm_content" ("core_type_id");

--
-- Add extensions table records
--
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(29, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_tags","type":"component","creationDate":"March 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2016 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"COM_TAGS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(315, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, '{"name":"mod_stats_admin","type":"module","creationDate":"September 2012","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"MOD_STATS_XML_DESCRIPTION","group":""}', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(316, 'mod_tags_popular', 'module', 'mod_tags_popular', '', 0, 1, 1, 0, '{"name":"mod_tags_popular","type":"module","creationDate":"January 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.1.0","description":"MOD_TAGS_POPULAR_XML_DESCRIPTION","group":""}', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(317, 'mod_tags_similar', 'module', 'mod_tags_similar', '', 0, 1, 1, 0, '{"name":"mod_tags_similar","type":"module","creationDate":"January 2013","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.1.0","description":"MOD_TAGS_SIMILAR_XML_DESCRIPTION","group":""}', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(447, 'plg_finder_tags', 'plugin', 'tags', 'finder', 0, 1, 1, 0, '{"name":"plg_finder_tags","type":"plugin","creationDate":"February 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2016 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.0.0","description":"PLG_FINDER_TAGS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

--
-- Add menu table records
--
INSERT INTO "#__menu" ("menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "checked_out", "checked_out_time", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id") VALUES
('main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 0, 1, 1, 29, 0, '1970-01-01 00:00:00', 0, 1, 'class:tags', 0, '', 45, 46, 0, '', 1);
