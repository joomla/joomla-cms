/* Changes to Smart Search tables for driver compatibility */
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [term_id] [bigint] NULL;
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [map_suffix] [nchar](1) NULL;
ALTER TABLE [#__finder_tokens_aggregate] ADD DEFAULT ((0)) FOR [term_id];
ALTER TABLE [#__finder_tokens_aggregate] ADD DEFAULT ((0)) FOR [total_weight];

/* Changes to tables where data type conflicts exist with MySQL (mainly dealing with null values */
ALTER TABLE [#__extensions] ADD DEFAULT (N'') FOR [system_data];
ALTER TABLE [#__modules] ADD DEFAULT (N'') FOR [content];
ALTER TABLE [#__updates] ADD DEFAULT (N'') FOR [data];

/* Tags database schema */

/****** Object:  Table [#__content_types] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__content_types] (
	[type_id] [bigint] IDENTITY(1,1) NOT NULL,
	[type_title] [nvarchar](255) NOT NULL DEFAULT '',
	[type_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[table] [nvarchar](255) NOT NULL DEFAULT '',
	[rules] [nvarchar](max) NOT NULL,
	[field_mappings] [nvarchar](max) NOT NULL,
	[router] [nvarchar](255) NOT NULL DEFAULT '',
 CONSTRAINT [PK_#__content_types_type_id] PRIMARY KEY CLUSTERED
(
	[type_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__content_types]
(
	[type_alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT #__content_types  ON;

INSERT INTO #__content_types ([type_id],[type_title],[type_alias],[table],[rules],[field_mappings],[router])
SELECT 1,'Article','com_content.article','{"special":{"dbtable":"#__content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}], "special": [{"fulltext":"fulltext"}]}','ContentHelperRoute::getArticleRoute'
UNION ALL
SELECT 2,'Contact','com_contact.contact','{"special":{"dbtable":"#__contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}], "special": [{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}]}','ContactHelperRoute::getContactRoute'
UNION ALL
SELECT 3,'Newsfeed','com_newsfeeds.newsfeed','{"special":{"dbtable":"#__newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}], "special": [{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}]}','NewsfeedsHelperRoute::getNewsfeedRoute'
UNION ALL
SELECT 4,'User','com_users.user','{"special":{"dbtable":"#__users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}], "special": [{}]}','UsersHelperRoute::getUserRoute'
UNION ALL
SELECT 5,'Article Category','com_content.category','{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','ContentHelperRoute::getCategoryRoute'
UNION ALL
SELECT 6,'Contact Category','com_contact.category','{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','ContactHelperRoute::getCategoryRoute'
UNION ALL
SELECT 7,'Newsfeeds Category','com_newsfeeds.category','{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}]}','NewsfeedsHelperRoute::getCategoryRoute'
UNION ALL
SELECT 8,'Tag','com_tags.tag','{"special":{"dbtable":"#__tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"#__core_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}','','{"common":[{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}], "special": [{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}]}','TagsHelperRoute::getTagRoute';

SET IDENTITY_INSERT #__content_types  OFF;


/****** Object:  Table [#__contentitem_tag_map] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__contentitem_tag_map] (
	[type_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[core_content_id] [bigint] NOT NULL,
	[content_item_id] [int] NOT NULL,
	[tag_id] [bigint] NOT NULL,
	[tag_date] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
 CONSTRAINT [#__contentitem_tag_map$uc_ItemnameTagid] UNIQUE NONCLUSTERED
(
	[type_alias] ASC,
	[content_item_id] ASC,
	[tag_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_tag_name] ON [#__contentitem_tag_map]
(
	[tag_id] ASC,
	[type_alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_date_id] ON [#__contentitem_tag_map]
(
	[tag_date] ASC,
	[tag_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_tag] ON [#__contentitem_tag_map]
(
	[tag_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_content_id] ON [#__contentitem_tag_map]
(
	[core_content_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Table [#__tags] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__tags] (
  [id] [int] IDENTITY(1,1) NOT NULL ,
  [parent_id] [bigint] NOT NULL DEFAULT '0',
  [lft] [int] NOT NULL DEFAULT '0',
  [rgt] [int] NOT NULL DEFAULT '0',
  [level] [bigint] NOT NULL DEFAULT '0',
  [path] [nvarchar](255) NOT NULL DEFAULT '',
  [title] [nvarchar](255) NOT NULL,
  [alias] [nvarchar](255) NOT NULL DEFAULT '',
  [note] [nvarchar](255) NOT NULL DEFAULT '',
  [description] [nvarchar](max) NOT NULL,
  [published] [smallint] NOT NULL DEFAULT '0',
  [checked_out] [bigint] NOT NULL DEFAULT '0',
  [checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [access] [int] NOT NULL DEFAULT '0',
  [params] [nvarchar](max) NOT NULL,
  [metadesc] [nvarchar](1024) NOT NULL,
  [metakey] [nvarchar](1024) NOT NULL,
  [metadata] [nvarchar](2048) NOT NULL,
  [created_user_id] [bigint] NOT NULL DEFAULT '0',
  [created_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [created_by_alias] [nvarchar](255) NOT NULL DEFAULT '',
  [modified_user_id] [bigint] NOT NULL DEFAULT '0',
  [modified_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [images] [nvarchar](max) NOT NULL,
  [urls] [nvarchar](max) NOT NULL,
  [hits] [bigint] NOT NULL DEFAULT '0',
  [language] [nvarchar](7) NOT NULL,
  [version] [bigint] NOT NULL DEFAULT '1',
  [publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  CONSTRAINT [PK_#__tags_id] PRIMARY KEY CLUSTERED
    (
      [id] ASC
    )WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [tag_idx] ON [#__tags]
(
  [published] ASC,
  [access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_access] ON [#__tags]
(
  [access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__tags]
(
  [checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_path] ON [#__tags]
(
  [path] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__tags]
(
  [lft] ASC,
  [rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__tags]
(
  [alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__tags]
(
  [language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT #__tags  ON;

INSERT INTO #__tags (id,parent_id,lft,rgt,level,path,title,alias,note,description,published,checked_out,checked_out_time,access,params,metadesc,metakey,metadata,created_user_id,created_time,modified_user_id,modified_time,images,urls,hits,language)
  SELECT 1,0,0,1,0,'','ROOT','root','','',1,0,'1900-01-01 00:00:00',1,'{}','','','',0,'2009-10-18 16:07:09',0,'1900-01-01 00:00:00','','',0,'*';

SET IDENTITY_INSERT #__tags  OFF;

/****** Object:  Table [#__ucm_base] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__ucm_base] (
  [ucm_id] [bigint] IDENTITY(1,1) NOT NULL,
  [ucm_item_id] [bigint] NOT NULL,
  [ucm_type_id] [bigint] NOT NULL,
  [ucm_language_id] [bigint] NOT NULL,
  CONSTRAINT [PK_#__ucm_base_ucm_id] PRIMARY KEY CLUSTERED
    (
      [ucm_id] ASC
    )WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY],
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [ucm_item_id] ON [#__ucm_base]
(
  [ucm_item_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [ucm_type_id] ON [#__ucm_base]
(
  [ucm_type_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [ucm_language_id] ON [#__ucm_base]
(
  [ucm_language_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__ucm_content] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__ucm_content] (
  [core_content_id] [bigint] IDENTITY(1,1) NOT NULL,
  [core_type_alias] [nvarchar](255) NOT NULL,
  [core_title] [nvarchar](255) NOT NULL DEFAULT '',
  [core_alias] [nvarchar](255) NOT NULL DEFAULT '',
  [core_body] [nvarchar](max) NOT NULL,
  [core_state] [smallint] NOT NULL DEFAULT '0',
  [core_checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [core_checked_out_user_id] [bigint] NOT NULL DEFAULT '0',
  [core_access] [bigint] NOT NULL DEFAULT '0',
  [core_params] [nvarchar](max) NOT NULL,
  [core_featured] [tinyint] NOT NULL DEFAULT '0',
  [core_metadata] [nvarchar](max) NOT NULL,
  [core_created_user_id] [bigint] NOT NULL DEFAULT '0',
  [core_created_by_alias] [nvarchar](255) NOT NULL DEFAULT '',
  [core_created_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [core_modified_user_id] [bigint] NOT NULL DEFAULT '0',
  [core_modified_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [core_language] [nvarchar](7) NOT NULL,
  [core_publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [core_publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
  [core_content_item_id] [bigint] NOT NULL DEFAULT '0',
  [asset_id] [bigint] NOT NULL DEFAULT '0',
  [core_images] [nvarchar](max) NOT NULL,
  [core_urls] [nvarchar](max) NOT NULL,
  [core_hits] [bigint] NOT NULL DEFAULT '0',
  [core_version] [bigint] NOT NULL DEFAULT '1',
  [core_ordering] [int] NOT NULL DEFAULT '0',
  [core_metakey] [nvarchar](max) NOT NULL,
  [core_metadesc] [nvarchar](max) NOT NULL,
  [core_catid] [bigint] NOT NULL DEFAULT '0',
  [core_xreference] [nvarchar](50) NOT NULL,
  [core_type_id] [bigint] NOT NULL DEFAULT '0',
  CONSTRAINT [PK_#__ucm_content_core_content_id] PRIMARY KEY CLUSTERED
    (
      [core_content_id] ASC
    )WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY],
  CONSTRAINT [#__ucm_content_core_content_id$idx_type_alias_item_id] UNIQUE NONCLUSTERED
    (
      [core_type_alias] ASC,
      [core_content_item_id] ASC
    )WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [tag_idx] ON [#__ucm_content]
(
  [core_state] ASC,
  [core_access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_access] ON [#__ucm_content]
(
  [core_access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__ucm_content]
(
  [core_alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__ucm_content]
(
  [core_language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_title] ON [#__ucm_content]
(
  [core_title] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_modified_time] ON [#__ucm_content]
(
  [core_modified_time] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_created_time] ON [#__ucm_content]
(
  [core_created_time] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_content_type] ON [#__ucm_content]
(
  [core_type_alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_modified_user_id] ON [#__ucm_content]
(
  [core_modified_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_checked_out_user_id] ON [#__ucm_content]
(
  [core_checked_out_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_created_user_id] ON [#__ucm_content]
(
  [core_created_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_type_id] ON [#__ucm_content]
(
  [core_type_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
  SELECT 29, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 1, '{"name":"com_joomlaupdate","type":"component","creationDate":"March 2013","author":"Joomla! Project","copyright":"(C) 2005 - 2015 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"3.1.0","description":"COM_TAGS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT #__extensions  OFF;

INSERT INTO #__menu (menutype, title, alias, note, path, link, type, published, parent_id, level, component_id, ordering, checked_out, checked_out_time, browserNav, access, img, template_style_id, params, lft, rgt, home, language, client_id)
  SELECT 'menu', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 0, 1, 1, 29, 0, '1900-01-01 00:00:00', 0, 0, 'class:tags', 0, '', 43, 44, 0, '*', 1

