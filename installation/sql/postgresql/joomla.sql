--
-- Table: #__assets
--
CREATE TABLE "#__assets" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer NOT NULL,
  "name" varchar(50) NOT NULL,
  "title" varchar(100) NOT NULL,
  "rules" varchar(5120) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__assets_idx_asset_name" UNIQUE ("name")
);
CREATE INDEX "#__assets_idx_lft_rgt" ON "#__assets" ("lft", "rgt");
CREATE INDEX "#__assets_idx_parent_id" ON "#__assets" ("parent_id");

COMMENT ON COLUMN "#__assets"."id" IS 'Primary Key';
COMMENT ON COLUMN "#__assets"."parent_id" IS 'Nested set parent.';
COMMENT ON COLUMN "#__assets"."lft" IS 'Nested set lft.';
COMMENT ON COLUMN "#__assets"."rgt" IS 'Nested set rgt.';
COMMENT ON COLUMN "#__assets"."level" IS 'The cached level in the nested tree.';
COMMENT ON COLUMN "#__assets"."name" IS 'The unique name for the asset.';
COMMENT ON COLUMN "#__assets"."title" IS 'The descriptive title for the asset.';
COMMENT ON COLUMN "#__assets"."rules" IS 'JSON encoded access control.';

--
-- Dumping data for table #__assets
--
INSERT INTO "#__assets" ("id", "parent_id", "lft", "rgt", "level", "name", "title", "rules") VALUES
(1, 0, 0, 103, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'),
(2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'),
(3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'),
(7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(8, 1, 17, 20, 1, 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.edit":{"4":1},"core.edit.state":{"5":1}}'),
(9, 1, 21, 22, 1, 'com_cpanel', 'com_cpanel', '{}'),
(10, 1, 23, 24, 1, 'com_installer', 'com_installer', '{"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'),
(11, 1, 25, 26, 1, 'com_languages', 'com_languages', '{"core.admin":{"7":1}}'),
(12, 1, 27, 28, 1, 'com_login', 'com_login', '{}'),
(13, 1, 29, 30, 1, 'com_mailto', 'com_mailto', '{}'),
(14, 1, 31, 32, 1, 'com_massmail', 'com_massmail', '{}'),
(15, 1, 33, 34, 1, 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'),
(16, 1, 35, 38, 1, 'com_menus', 'com_menus', '{"core.admin":{"7":1}}'),
(17, 1, 39, 40, 1, 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(18, 1, 41, 72, 1, 'com_modules', 'com_modules', '{"core.admin":{"7":1}}'),
(19, 1, 73, 76, 1, 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(20, 1, 77, 78, 1, 'com_plugins', 'com_plugins', '{"core.admin":{"7":1}}'),
(21, 1, 79, 80, 1, 'com_redirect', 'com_redirect', '{"core.admin":{"7":1}}'),
(22, 1, 81, 82, 1, 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(23, 1, 83, 84, 1, 'com_templates', 'com_templates', '{"core.admin":{"7":1}}'),
(24, 1, 85, 88, 1, 'com_users', 'com_users', '{"core.admin":{"7":1}}'),
(26, 1, 89, 90, 1, 'com_wrapper', 'com_wrapper', '{}'),
(27, 8, 18, 19, 2, 'com_content.category.2', 'Uncategorised', '{}'),
(28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{}'),
(29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{}'),
(30, 19, 74, 75, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{}'),
(32, 24, 86, 87, 2, 'com_users.category.7', 'Uncategorised', '{}'),
(33, 1, 91, 92, 1, 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(34, 1, 93, 94, 1, 'com_joomlaupdate', 'com_joomlaupdate', '{}'),
(35, 1, 95, 96, 1, 'com_tags', 'com_tags', '{}'),
(36, 1, 97, 98, 1, 'com_contenthistory', 'com_contenthistory', '{}'),
(37, 1, 99, 100, 1, 'com_ajax', 'com_ajax', '{}'),
(38, 1, 101, 102, 1, 'com_postinstall', 'com_postinstall', '{}'),
(39, 18, 42, 43, 2, 'com_modules.module.1', 'Main Menu', '{}'),
(40, 18, 44, 45, 2, 'com_modules.module.2', 'Login', '{}'),
(41, 18, 46, 47, 2, 'com_modules.module.3', 'Popular Articles', '{}'),
(42, 18, 48, 49, 2, 'com_modules.module.4', 'Recently Added Articles', '{}'),
(43, 18, 50, 51, 2, 'com_modules.module.8', 'Toolbar', '{}'),
(44, 18, 52, 53, 2, 'com_modules.module.9', 'Quick Icons', '{}'),
(45, 18, 54, 55, 2, 'com_modules.module.10', 'Logged-in Users', '{}'),
(46, 18, 56, 57, 2, 'com_modules.module.12', 'Admin Menu', '{}'),
(47, 18, 58, 59, 2, 'com_modules.module.13', 'Admin Submenu', '{}'),
(48, 18, 60, 61, 2, 'com_modules.module.14', 'User Status', '{}'),
(49, 18, 62, 63, 2, 'com_modules.module.15', 'Title', '{}'),
(50, 18, 64, 65, 2, 'com_modules.module.16', 'Login Form', '{}'),
(51, 18, 66, 67, 2, 'com_modules.module.17', 'Breadcrumbs', '{}'),
(52, 18, 68, 69, 2, 'com_modules.module.79', 'Multilanguage status', '{}'),
(53, 18, 70, 71, 2, 'com_modules.module.86', 'Joomla Version', '{}'),
(54, 16, 36, 37, 2, 'com_menus.menu.1', 'Main Menu', '{}');

SELECT setval('#__assets_id_seq', 55, false);

--
-- Table: #__associations
--
CREATE TABLE "#__associations" (
  "id" int NOT NULL,
  "context" varchar(50) NOT NULL,
  "key" char(32) NOT NULL,
  CONSTRAINT "#__associations_idx_context_id" PRIMARY KEY ("context", "id")
);
CREATE INDEX "#__associations_idx_key" ON "#__associations" ("key");

COMMENT ON COLUMN "#__associations"."id" IS 'A reference to the associated item.';
COMMENT ON COLUMN "#__associations"."context" IS 'The context of the associated item.';
COMMENT ON COLUMN "#__associations"."key" IS 'The key for the association computed from an md5 on associated ids.';

--
-- Table: #__banners
--
CREATE TABLE "#__banners" (
  "id" serial NOT NULL,
  "cid" bigint DEFAULT 0 NOT NULL,
  "type" bigint DEFAULT 0 NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "imptotal" bigint DEFAULT 0 NOT NULL,
  "impmade" bigint DEFAULT 0 NOT NULL,
  "clicks" bigint DEFAULT 0 NOT NULL,
  "clickurl" varchar(200) DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "catid" bigint DEFAULT 0 NOT NULL,
  "description" text NOT NULL,
  "custombannercode" varchar(2048) NOT NULL,
  "sticky" smallint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "metakey" text NOT NULL,
  "params" text NOT NULL,
  "own_prefix" smallint DEFAULT 0 NOT NULL,
  "metakey_prefix" varchar(255) DEFAULT '' NOT NULL,
  "purchase_type" smallint DEFAULT -1 NOT NULL,
  "track_clicks" smallint DEFAULT -1 NOT NULL,
  "track_impressions" smallint DEFAULT -1 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "reset" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__banners_idx_state" ON "#__banners" ("state");
CREATE INDEX "#__banners_idx_own_prefix" ON "#__banners" ("own_prefix");
CREATE INDEX "#__banners_idx_metakey_prefix" ON "#__banners" ("metakey_prefix");
CREATE INDEX "#__banners_idx_banner_catid" ON "#__banners" ("catid");
CREATE INDEX "#__banners_idx_language" ON "#__banners" ("language");

--
-- Table: #__banner_clients
--
CREATE TABLE "#__banner_clients" (
  "id" serial NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "contact" varchar(255) DEFAULT '' NOT NULL,
  "email" varchar(255) DEFAULT '' NOT NULL,
  "extrainfo" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "metakey" text NOT NULL,
  "own_prefix" smallint DEFAULT 0 NOT NULL,
  "metakey_prefix" varchar(255) DEFAULT '' NOT NULL,
  "purchase_type" smallint DEFAULT -1 NOT NULL,
  "track_clicks" smallint DEFAULT -1 NOT NULL,
  "track_impressions" smallint DEFAULT -1 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__banner_clients_idx_own_prefix" ON "#__banner_clients" ("own_prefix");
CREATE INDEX "#__banner_clients_idx_metakey_prefix" ON "#__banner_clients" ("metakey_prefix");

--
-- Table: #__banner_tracks
--
CREATE TABLE "#__banner_tracks" (
  "track_date" timestamp without time zone NOT NULL,
  "track_type" bigint NOT NULL,
  "banner_id" bigint NOT NULL,
  "count" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("track_date", "track_type", "banner_id")
);
CREATE INDEX "#__banner_tracks_idx_track_date" ON "#__banner_tracks" ("track_date");
CREATE INDEX "#__banner_tracks_idx_track_type" ON "#__banner_tracks" ("track_type");
CREATE INDEX "#__banner_tracks_idx_banner_id" ON "#__banner_tracks" ("banner_id");

--
-- Table: #__categories
--
CREATE TABLE "#__categories" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "parent_id" integer DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" varchar(255) DEFAULT '' NOT NULL,
  "extension" varchar(50) DEFAULT '' NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "metadesc" varchar(1024) NOT NULL,
  "metakey" varchar(1024) NOT NULL,
  "metadata" varchar(2048) NOT NULL,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_user_id" integer DEFAULT 0 NOT NULL,
  "modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "hits" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__categories_cat_idx" ON "#__categories" ("extension", "published", "access");
CREATE INDEX "#__categories_idx_access" ON "#__categories" ("access");
CREATE INDEX "#__categories_idx_checkout" ON "#__categories" ("checked_out");
CREATE INDEX "#__categories_idx_path" ON "#__categories" ("path");
CREATE INDEX "#__categories_idx_left_right" ON "#__categories" ("lft", "rgt");
CREATE INDEX "#__categories_idx_alias" ON "#__categories" ("alias");
CREATE INDEX "#__categories_idx_language" ON "#__categories" ("language");

COMMENT ON COLUMN "#__categories"."asset_id" IS 'FK to the #__assets table.';
COMMENT ON COLUMN "#__categories"."metadesc" IS 'The meta description for the page.';
COMMENT ON COLUMN "#__categories"."metakey" IS 'The meta keywords for the page.';
COMMENT ON COLUMN "#__categories"."metadata" IS 'JSON encoded metadata properties.';

--
-- Dumping data for table #__categories
--
INSERT INTO "#__categories" ("id", "asset_id", "parent_id", "lft", "rgt", "level", "path", "extension", "title", "alias", "note", "description", "published", "checked_out", "checked_out_time", "access", "params", "metadesc", "metakey", "metadata", "created_user_id", "created_time", "modified_user_id", "modified_time", "hits", "language", "version") VALUES
(1, 0, 0, 0, 11, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '1970-01-01 00:00:00', 1, '{}', '', '', '{}', 42, '2011-01-01 00:00:01', 0, '1970-01-01 00:00:00', 0, '*', 1),
(2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1970-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1970-01-01 00:00:00', 0, '*', 1),
(3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1970-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1970-01-01 00:00:00', 0, '*', 1),
(4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1970-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1970-01-01 00:00:00', 0, '*', 1),
(5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1970-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1970-01-01 00:00:00', 0, '*', 1),
(7, 32, 1, 9, 10, 1, 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1970-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1970-01-01 00:00:00', 0, '*', 1);

SELECT setval('#__categories_id_seq', 8, false);

--
-- Table: #__contact_details
--
CREATE TABLE "#__contact_details" (
  "id" serial NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "con_position" varchar(255) DEFAULT NULL,
  "address" text,
  "suburb" varchar(100) DEFAULT NULL,
  "state" varchar(100) DEFAULT NULL,
  "country" varchar(100) DEFAULT NULL,
  "postcode" varchar(100) DEFAULT NULL,
  "telephone" varchar(255) DEFAULT NULL,
  "fax" varchar(255) DEFAULT NULL,
  "misc" text,
  "image" varchar(255) DEFAULT NULL,
  "email_to" varchar(255) DEFAULT NULL,
  "default_con" smallint DEFAULT 0 NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "user_id" bigint DEFAULT 0 NOT NULL,
  "catid" bigint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "mobile" varchar(255) DEFAULT '' NOT NULL,
  "webpage" varchar(255) DEFAULT '' NOT NULL,
  "sortname1" varchar(255) NOT NULL,
  "sortname2" varchar(255) NOT NULL,
  "sortname3" varchar(255) NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" integer DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  "metakey" text NOT NULL,
  "metadesc" text NOT NULL,
  "metadata" text NOT NULL,
  "featured" smallint DEFAULT 0 NOT NULL,
  "xreference" varchar(50) NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__contact_details_idx_access" ON "#__contact_details" ("access");
CREATE INDEX "#__contact_details_idx_checkout" ON "#__contact_details" ("checked_out");
CREATE INDEX "#__contact_details_idx_state" ON "#__contact_details" ("published");
CREATE INDEX "#__contact_details_idx_catid" ON "#__contact_details" ("catid");
CREATE INDEX "#__contact_details_idx_createdby" ON "#__contact_details" ("created_by");
CREATE INDEX "#__contact_details_idx_featured_catid" ON "#__contact_details" ("featured", "catid");
CREATE INDEX "#__contact_details_idx_language" ON "#__contact_details" ("language");
CREATE INDEX "#__contact_details_idx_xreference" ON "#__contact_details" ("xreference");

COMMENT ON COLUMN "#__contact_details"."featured" IS 'Set if contact is featured.';
COMMENT ON COLUMN "#__contact_details"."xreference" IS 'A reference to enable linkages to external data sets.';

--
-- Table: #__content
--
CREATE TABLE "#__content" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "introtext" text NOT NULL,
  "fulltext" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "catid" bigint DEFAULT 0 NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "images" text NOT NULL,
  "urls" text NOT NULL,
  "attribs" varchar(5120) NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "metakey" text NOT NULL,
  "metadesc" text NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  "metadata" text NOT NULL,
  "featured" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "xreference" varchar(50) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__content_idx_access" ON "#__content" ("access");
CREATE INDEX "#__content_idx_checkout" ON "#__content" ("checked_out");
CREATE INDEX "#__content_idx_state" ON "#__content" ("state");
CREATE INDEX "#__content_idx_catid" ON "#__content" ("catid");
CREATE INDEX "#__content_idx_createdby" ON "#__content" ("created_by");
CREATE INDEX "#__content_idx_featured_catid" ON "#__content" ("featured", "catid");
CREATE INDEX "#__content_idx_language" ON "#__content" ("language");
CREATE INDEX "#__content_idx_xreference" ON "#__content" ("xreference");

COMMENT ON COLUMN "#__content"."asset_id" IS 'FK to the #__assets table.';
COMMENT ON COLUMN "#__content"."featured" IS 'Set if article is featured.';
COMMENT ON COLUMN "#__content"."language" IS 'The language code for the article.';
COMMENT ON COLUMN "#__content"."xreference" IS 'A reference to enable linkages to external data sets.';

--
-- Table: #__content_frontpage
--
CREATE TABLE "#__content_frontpage" (
  "content_id" bigint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("content_id")
);

--
-- Table: #__content_rating
--
CREATE TABLE "#__content_rating" (
  "content_id" bigint DEFAULT 0 NOT NULL,
  "rating_sum" bigint DEFAULT 0 NOT NULL,
  "rating_count" bigint DEFAULT 0 NOT NULL,
  "lastip" varchar(50) DEFAULT '' NOT NULL,
  PRIMARY KEY ("content_id")
);

--
-- Table: #__content_types
--
CREATE TABLE "#__content_types" (
  "type_id" serial NOT NULL,
  "type_title" varchar(255) NOT NULL DEFAULT '',
  "type_alias" varchar(255) NOT NULL DEFAULT '',
  "table" varchar(255) NOT NULL DEFAULT '',
  "rules" text NOT NULL,
  "field_mappings" text NOT NULL,
  "router" varchar(255) NOT NULL DEFAULT '',
  "content_history_options" varchar(5120) DEFAULT NULL,
  PRIMARY KEY ("type_id")
);
CREATE INDEX "#__content_types_idx_alias" ON "#__content_types" ("type_alias");

COMMENT ON COLUMN "#__content_types"."content_history_options" IS 'JSON string for com_contenthistory options';

--
-- Dumping data for table #__content_types
--
INSERT INTO "#__content_types" ("type_id", "type_title", "type_alias", "table", "rules", "field_mappings", "router", "content_history_options") VALUES
(1, 'Article', 'com_content.article', '{"special":{"dbtable":"#__content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}, "special":{"fulltext":"fulltext"}}', 'ContentHelperRoute::getArticleRoute', '{"formFile":"administrator\\/components\\/com_content\\/models\\/forms\\/article.xml", "hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'),
(2, 'Contact', 'com_contact.contact', '{"special":{"dbtable":"#__contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}}', 'ContactHelperRoute::getContactRoute', '{"formFile":"administrator\\/components\\/com_contact\\/models\\/forms\\/contact.xml","hideFields":["default_con","checked_out","checked_out_time","version","xreference"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[ {"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ] }'),
(3, 'Newsfeed', 'com_newsfeeds.newsfeed', '{"special":{"dbtable":"#__newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}}', 'NewsfeedsHelperRoute::getNewsfeedRoute', '{"formFile":"administrator\\/components\\/com_newsfeeds\\/models\\/forms\\/newsfeed.xml","hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'),
(4, 'User', 'com_users.user', '{"special":{"dbtable":"#__users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{}}', 'UsersHelperRoute::getUserRoute', ''),
(5, 'Article Category', 'com_content.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContentHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'),
(6, 'Contact Category', 'com_contact.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContactHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'),
(7, 'Newsfeeds Category', 'com_newsfeeds.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'NewsfeedsHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'),
(8, 'Tag', 'com_tags.tag', '{"special":{"dbtable":"#__tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}}', 'TagsHelperRoute::getTagRoute', '{"formFile":"administrator\\/components\\/com_tags\\/models\\/forms\\/tag.xml", "hideFields":["checked_out","checked_out_time","version", "lft", "rgt", "level", "path", "urls", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}'),
(9, 'Banner', 'com_banners.banner', '{"special":{"dbtable":"#__banners","key":"id","type":"Banner","prefix":"BannersTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"null","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"null"}, "special":{"imptotal":"imptotal", "impmade":"impmade", "clicks":"clicks", "clickurl":"clickurl", "custombannercode":"custombannercode", "cid":"cid", "purchase_type":"purchase_type", "track_impressions":"track_impressions", "track_clicks":"track_clicks"}}', '','{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/banner.xml", "hideFields":["checked_out","checked_out_time","version", "reset"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "imptotal", "impmade", "reset"], "convertToInt":["publish_up", "publish_down", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"cid","targetTable":"#__banner_clients","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'),
(10, 'Banners Category', 'com_banners.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}','','{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'),
(11, 'Banner Client', 'com_banners.client', '{"special":{"dbtable":"#__banner_clients","key":"id","type":"Client","prefix":"BannersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_banners\\/models\\/forms\\/client.xml", "hideFields":["checked_out","checked_out_time"], "ignoreChanges":["checked_out", "checked_out_time"], "convertToInt":[], "displayLookup":[]}'),
(12, 'User Notes', 'com_users.note', '{"special":{"dbtable":"#__user_notes","key":"id","type":"Note","prefix":"UsersTable"}}', '', '', '', '{"formFile":"administrator\\/components\\/com_users\\/models\\/forms\\/note.xml", "hideFields":["checked_out","checked_out_time", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}'),
(13, 'User Notes Category', 'com_users.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}');

SELECT setval('#__content_types_type_id_seq', 10000, false);

--
-- Table: #__contentitem_tag_map
--
CREATE TABLE "#__contentitem_tag_map" (
  "type_alias" varchar(255) NOT NULL DEFAULT '',
  "core_content_id" integer NOT NULL,
  "content_item_id" integer NOT NULL,
  "tag_id" integer NOT NULL,
  "tag_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "type_id" integer NOT NULL,
 CONSTRAINT "#__uc_ItemnameTagid" UNIQUE ("type_id", "content_item_id", "tag_id")
);
CREATE INDEX "#__contentitem_tag_map_idx_tag_type" ON "#__contentitem_tag_map" ("tag_id", "type_id");
CREATE INDEX "#__contentitem_tag_map_idx_date_id" ON "#__contentitem_tag_map" ("tag_date", "tag_id");
CREATE INDEX "#__contentitem_tag_map_idx_core_content_id" ON "#__contentitem_tag_map" ("core_content_id");

COMMENT ON COLUMN "#__contentitem_tag_map"."core_content_id" IS 'PK from the core content table';
COMMENT ON COLUMN "#__contentitem_tag_map"."content_item_id" IS 'PK from the content type table';
COMMENT ON COLUMN "#__contentitem_tag_map"."tag_id" IS 'PK from the tag table';
COMMENT ON COLUMN "#__contentitem_tag_map"."tag_date" IS 'Date of most recent save for this tag-item';
COMMENT ON COLUMN "#__contentitem_tag_map"."type_id" IS 'PK from the content_type table';

-- --------------------------------------------------------

--
-- Table: #__core_log_searches
--
CREATE TABLE "#__core_log_searches" (
  "search_term" varchar(128) DEFAULT '' NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL
);

--
-- Table: #__extensions
--
CREATE TABLE "#__extensions" (
  "extension_id" serial NOT NULL,
  "package_id" bigint DEFAULT 0 NOT NULL,
  "name" varchar(100) NOT NULL,
  "type" varchar(20) NOT NULL,
  "element" varchar(100) NOT NULL,
  "folder" varchar(100) NOT NULL,
  "client_id" smallint NOT NULL,
  "enabled" smallint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 1 NOT NULL,
  "protected" smallint DEFAULT 0 NOT NULL,
  "manifest_cache" text NOT NULL,
  "params" text NOT NULL,
  "custom_data" text DEFAULT '' NOT NULL,
  "system_data" text DEFAULT '' NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "ordering" bigint DEFAULT 0,
  "state" bigint DEFAULT 0,
  PRIMARY KEY ("extension_id")
);
CREATE INDEX "#__extensions_element_clientid" ON "#__extensions" ("element", "client_id");
CREATE INDEX "#__extensions_element_folder_clientid" ON "#__extensions" ("element", "folder", "client_id");
CREATE INDEX "#__extensions_extension" ON "#__extensions" ("type", "element", "folder", "client_id");

COMMENT ON COLUMN "#__extensions"."package_id" IS 'Parent package ID for extensions installed as a package.';

-- Components
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(1, 'com_mailto', 'component', 'com_mailto', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(2, 'com_wrapper', 'component', 'com_wrapper', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(3, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(4, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, '', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":"","save_history":"1","history_limit":10}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(5, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(6, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(7, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(8, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, '', '{"show_contact_category":"hide","save_history":"1","history_limit":10,"show_contact_list":"0","presentation_style":"sliders","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","image":"","allow_vcard":"0","show_articles":"0","show_profile":"0","show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","show_headings":"1","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","allow_vcard_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_category_crumb":"0","metakey":"","metadesc":"","robots":"","author":"","rights":"","xreference":""}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(9, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(10, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, '', '{"show_jed_info":"1","cachetimeout":"6","minimum_stability":"4"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(11, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, '', '{"administrator":"en-GB","site":"en-GB"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(12, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(13, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, '', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,application\\/x-shockwave-flash,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/x-zip","upload_mime_illegal":"text\\/html"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(14, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, '', '{"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":""}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(15, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(16, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(17, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, '', '{"newsfeed_layout":"_:default","save_history":"1","history_limit":5,"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_character_count":"0","feed_display_order":"des","float_first":"right","float_second":"right","show_tags":"1","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"-1","show_empty_categories":"0","show_subcat_desc":"1","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_items_cat":"1","filter_field":"1","show_pagination_limit":"1","show_headings":"1","show_articles":"0","show_link":"1","show_pagination":"1","show_pagination_results":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(18, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(19, 'com_search', 'component', 'com_search', '', 1, 1, 1, 0, '', '{"enabled":"0","search_phrases":"1","search_areas":"1","show_date":"1","opensearch_name":"","opensearch_description":""}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(20, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, '', '{"template_positions_display":"0","upload_limit":"10","image_formats":"gif,bmp,jpg,jpeg,png","source_formats":"txt,less,ini,xml,js,php,css,scss,sass","font_formats":"woff,ttf,otf","compressed_formats":"zip"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(22, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, '', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","info_block_position":"0","info_block_show_title":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_associations":"0","flags":"1","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_tags":"1","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","urls_position":"0","captcha":"0","show_publishing_options":"1","show_article_options":"1","save_history":"1","history_limit":10,"show_urls_images_frontend":"0","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_heading_title_text":"1","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_subcategory_content":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","list_show_votes":"0","list_show_ratings":"0","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_featured":"show","show_feed_link":"1","feed_summary":"0","feed_show_readmore":"0","sef_advanced":0,"sef_ids":0,"custom_fields_enable":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(23, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, '', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"NONE","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(24, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(25, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, '', '{"allowUserRegistration":"0","new_usertype":"2","guest_usergroup":"9","sendpassword":"1","useractivation":"2","mail_to_admin":"1","captcha":"","frontend_userparams":"1","site_language":"0","change_login_name":"0","reset_count":"10","reset_time":"1","minimum_length":"4","minimum_integers":"0","minimum_symbols":"0","minimum_uppercase":"0","save_history":"1","history_limit":5,"mailSubjectPrefix":"","mailBodySuffix":""}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(27, 'com_finder', 'component', 'com_finder', '', 1, 1, 0, 0, '', '{"show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_advanced":"1","expand_advanced":"0","show_date_filters":"0","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","memory_table_limit":30000,"title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stemmer":"snowball"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(28, 'com_joomlaupdate', 'component', 'com_joomlaupdate', '', 1, 1, 0, 1, '', '{"updatesource":"default","customurl":""}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(29, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 1, '', '{"tag_layout":"_:default","save_history":"1","history_limit":5,"show_tag_title":"0","tag_list_show_tag_image":"0","tag_list_show_tag_description":"0","tag_list_image":"","tag_list_orderby":"title","tag_list_orderby_direction":"ASC","show_headings":"0","tag_list_show_date":"0","tag_list_show_item_image":"0","tag_list_show_item_description":"0","tag_list_item_maximum_characters":0,"return_any_or_all":"1","include_children":"0","maximum":200,"tag_list_language_filter":"all","tags_layout":"_:default","all_tags_orderby":"title","all_tags_orderby_direction":"ASC","all_tags_show_tag_image":"0","all_tags_show_tag_descripion":"0","all_tags_tag_maximum_characters":20,"all_tags_show_tag_hits":"0","filter_field":"1","show_pagination_limit":"1","show_pagination":"2","show_pagination_results":"1","tag_field_ajax_mode":"1","show_feed_link":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(30, 'com_contenthistory', 'component', 'com_contenthistory', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(32, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(33, 'com_fields', 'component', 'com_fields', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Libraries
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(103, 'Joomla! Platform', 'library', 'joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(104, 'IDNA Convert', 'library', 'idna_convert', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(106, 'PHPass', 'library', 'phpass', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Modules
-- Site
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(200, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(201, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(202, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(203, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(204, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(205, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(206, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(207, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(208, 'mod_login', 'module', 'mod_login', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(209, 'mod_menu', 'module', 'mod_menu', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(210, 'mod_articles_news', 'module', 'mod_articles_news', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(211, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(212, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(213, 'mod_search', 'module', 'mod_search', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(214, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(215, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(216, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(218, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(219, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(220, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(221, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(222, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(223, 'mod_finder', 'module', 'mod_finder', '', 0, 1, 0, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Administrator
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(300, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(301, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(302, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(303, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(304, 'mod_login', 'module', 'mod_login', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(305, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(307, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(308, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(309, 'mod_status', 'module', 'mod_status', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(310, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(311, 'mod_title', 'module', 'mod_title', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(312, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(313, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, '', '{"cache":"0"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(314, 'mod_version', 'module', 'mod_version', '', 1, 1, 1, 0, '', '{"format":"short","product":"1","cache":"0"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(315, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, '', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(316, 'mod_tags_popular', 'module', 'mod_tags_popular', '', 0, 1, 1, 0, '', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(317, 'mod_tags_similar', 'module', 'mod_tags_similar', '', 0, 1, 1, 0, '', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Plugins
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(400, 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(401, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(402, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', 0, '1970-01-01 00:00:00', 3, 0),
(403, 'plg_content_contact', 'plugin', 'contact', 'content', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(404, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '', '{"mode":"1"}', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(406, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '', '{"style":"xhtml"}', '', '', 0, '2011-09-18 15:22:50', 0, 0),
(407, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 0, '', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', 0, '1970-01-01 00:00:00', 4, 0),
(408, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 0, '', '{"position":"1"}', '', '', 0, '1970-01-01 00:00:00', 5, 0),
(409, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 6, 0),
(410, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '', '{"lineNumbers":"1","lineWrapping":"1","matchTags":"1","matchBrackets":"1","marker-gutter":"1","autoCloseTags":"1","autoCloseBrackets":"1","autoFocus":"1","theme":"default","tabmode":"indent"}', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(411, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 2, 0),
(412, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 0, '', '{"configuration":{"toolbars":{"2":{"toolbar1":["bold","underline","strikethrough","|","undo","redo","|","bullist","numlist","|","pastetext"]},"1":{"menu":["edit","insert","view","format","table","tools"],"toolbar1":["bold","italic","underline","strikethrough","|","alignleft","aligncenter","alignright","alignjustify","|","formatselect","|","bullist","numlist","|","outdent","indent","|","undo","redo","|","link","unlink","anchor","code","|","hr","table","|","subscript","superscript","|","charmap","pastetext","preview"]},"0":{"menu":["edit","insert","view","format","table","tools"],"toolbar1":["bold","italic","underline","strikethrough","|","alignleft","aligncenter","alignright","alignjustify","|","styleselect","|","formatselect","fontselect","fontsizeselect","|","searchreplace","|","bullist","numlist","|","outdent","indent","|","undo","redo","|","link","unlink","anchor","image","|","code","|","forecolor","backcolor","|","fullscreen","|","table","|","subscript","superscript","|","charmap","emoticons","media","hr","ltr","rtl","|","cut","copy","paste","pastetext","|","visualchars","visualblocks","nonbreaking","blockquote","template","|","print","preview","codesample","insertdatetime","removeformat"]}},"setoptions":{"2":{"access":["1"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"0","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""},"1":{"access":["6","2"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"0","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""},"0":{"access":["7","4","8"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"0","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""}}},"sets_amount":3,"html_height":"550","html_width":"750"}', '', '', 0, '1970-01-01 00:00:00', 3, 0),
(413, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(414, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 2, 0),
(415, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 3, 0),
(416, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 4, 0),
(417, 'plg_search_categories', 'plugin', 'categories', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(418, 'plg_search_contacts', 'plugin', 'contacts', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(419, 'plg_search_content', 'plugin', 'content', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(420, 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(422, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(423, 'plg_system_p3p', 'plugin', 'p3p', 'system', 0, 0, 1, 0, '', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', 0, '1970-01-01 00:00:00', 2, 0),
(424, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 1, '', '{"browsercache":"0","cachetime":"15"}', '', '', 0, '1970-01-01 00:00:00', 9, 0),
(425, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', 0, '1970-01-01 00:00:00', 4, 0),
(426, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 5, 0),
(427, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 0, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 6, 0),
(428, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 7, 0),
(429, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 8, 0),
(430, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 3, 0),
(431, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 0, '', '{"autowebpage":"","category":"34","autopublish":"0"}', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(432, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '', '{"autoregister":"1","mail_to_user":"1","forceLogout":"1"}', '', '', 0, '1970-01-01 00:00:00', 2, 0),
(433, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 0, '', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(434, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(436, 'plg_system_languagecode', 'plugin', 'languagecode', 'system', 0, 0, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 10, 0),
(437, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(438, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(439, 'plg_captcha_recaptcha', 'plugin', 'recaptcha', 'captcha', 0, 0, 1, 0, '', '{"public_key":"","private_key":"","theme":"clean"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(440, 'plg_system_highlight', 'plugin', 'highlight', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 7, 0),
(441, 'plg_content_finder', 'plugin', 'finder', 'content', 0, 0, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(442, 'plg_finder_categories', 'plugin', 'categories', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(443, 'plg_finder_contacts', 'plugin', 'contacts', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 2, 0),
(444, 'plg_finder_content', 'plugin', 'content', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 3, 0),
(445, 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 4, 0),
(447, 'plg_finder_tags', 'plugin', 'tags', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(449, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(450, 'plg_twofactorauth_yubikey', 'plugin', 'yubikey', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(451, 'plg_search_tags', 'plugin', 'tags', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","show_tagged_items":"1"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(452, 'plg_system_updatenotification', 'plugin', 'updatenotification', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(453, 'plg_editors-xtd_module', 'plugin', 'module', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(454, 'plg_system_stats', 'plugin', 'stats', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(455, 'plg_installer_packageinstaller', 'plugin', 'packageinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 1, 0),
(456, 'plg_installer_folderinstaller', 'plugin', 'folderinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 2, 0),
(457, 'plg_installer_urlinstaller', 'plugin', 'urlinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 3, 0),
(458, 'plg_quickicon_phpversioncheck', 'plugin', 'phpversioncheck', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(459, 'plg_editors-xtd_menu', 'plugin', 'menu', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(460, 'plg_editors-xtd_contact', 'plugin', 'contact', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(461, 'plg_system_fields', 'plugin', 'fields', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(462, 'plg_fields_calendar', 'plugin', 'calendar', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(463, 'plg_fields_checkboxes', 'plugin', 'checkboxes', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(464, 'plg_fields_color', 'plugin', 'color', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(465, 'plg_fields_editor', 'plugin', 'editor', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(466, 'plg_fields_gallery', 'plugin', 'gallery', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(467, 'plg_fields_imagelist', 'plugin', 'imagelist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(468, 'plg_fields_integer', 'plugin', 'integer', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(469, 'plg_fields_list', 'plugin', 'list', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(470, 'plg_fields_media', 'plugin', 'media', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(471, 'plg_fields_radio', 'plugin', 'radio', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(472, 'plg_fields_sql', 'plugin', 'sql', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(473, 'plg_fields_text', 'plugin', 'text', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(474, 'plg_fields_textarea', 'plugin', 'textarea', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(475, 'plg_fields_url', 'plugin', 'url', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(476, 'plg_fields_user', 'plugin', 'user', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(477, 'plg_fields_usergrouplist', 'plugin', 'usergrouplist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Templates
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(503, 'beez3', 'template', 'beez3', '', 0, 1, 1, 0, '', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, '', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(506, 'protostar', 'template', 'protostar', '', 0, 1, 1, 0, '', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(507, 'isis', 'template', 'isis', '', 1, 1, 1, 0, '', '{"templateColor":"","logoFile":""}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Languages
INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(600, 802, 'English (en-GB)', 'language', 'en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(601, 802, 'English (en-GB)', 'language', 'en-GB', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Files Extensions
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(700, 'files_joomla', 'file', 'joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

-- Packages
INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(802, 'English (en-GB) Language Pack', 'package', 'pkg_en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

SELECT setval('#__extensions_extension_id_seq', 10000, false);

--
-- Table: #__fields
--
CREATE TABLE "#__fields" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "context" varchar(255) DEFAULT '' NOT NULL,
  "group_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "label" varchar(255) DEFAULT '' NOT NULL,
  "default_value" text DEFAULT '' NOT NULL,
  "type" varchar(255) DEFAULT 'text' NOT NULL,
  "options" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "required" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "params" text DEFAULT '' NOT NULL,
  "fieldparams" text DEFAULT '' NOT NULL,
  "attributes" text DEFAULT '' NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_user_id" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__fields_idx_checked_out" ON "#__fields" ("checked_out");
CREATE INDEX "#__fields_idx_state" ON "#__fields" ("state");
CREATE INDEX "#__fields_idx_created_user_id" ON "#__fields" ("created_user_id");
CREATE INDEX "#__fields_idx_access" ON "#__fields" ("access");
CREATE INDEX "#__fields_idx_context" ON "#__fields" ("context");
CREATE INDEX "#__fields_idx_language" ON "#__fields" ("language");

--
-- Table: #__fields_categories
--
CREATE TABLE "#__fields_categories" (
  "field_id" bigint DEFAULT 0 NOT NULL,
  "category_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("field_id", "category_id")
);

--
-- Table: #__fields_groups
--

CREATE TABLE "#__fields_groups" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "context" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text NOT NULL,
  "state" smallint DEFAULT '0' NOT NULL,
  "checked_out" integer DEFAULT '0' NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "ordering" integer DEFAULT '0' NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT '0' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT '0' NOT NULL,
  "access" bigint DEFAULT '1' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__fields_groups_idx_checked_out" ON "#__fields_groups" ("checked_out");
CREATE INDEX "#__fields_groups_idx_state" ON "#__fields_groups" ("state");
CREATE INDEX "#__fields_groups_idx_created_by" ON "#__fields_groups" ("created_by");
CREATE INDEX "#__fields_groups_idx_access" ON "#__fields_groups" ("access");
CREATE INDEX "#__fields_groups_idx_context" ON "#__fields_groups" ("context");
CREATE INDEX "#__fields_groups_idx_language" ON "#__fields_groups" ("language");

--
-- Table: #__fields_values
--
CREATE TABLE "#__fields_values" (
"field_id" bigint DEFAULT 0 NOT NULL,
"item_id" varchar(255) DEFAULT '' NOT NULL,
"context" varchar(255) DEFAULT '' NOT NULL,
"value" text DEFAULT '' NOT NULL
);
CREATE INDEX "#__fields_values_idx_field_id" ON "#__fields_values" ("field_id");
CREATE INDEX "#__fields_values_idx_context" ON "#__fields_values" ("context");
CREATE INDEX "#__fields_values_idx_item_id" ON "#__fields_values" ("item_id");

--
-- Table: #__finder_filters
--
CREATE TABLE "#__finder_filters" (
  "filter_id" serial NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" integer NOT NULL,
  "created_by_alias" varchar(255) NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "map_count" integer DEFAULT 0 NOT NULL,
  "data" text NOT NULL,
  "params" text,
  PRIMARY KEY ("filter_id")
);

--
-- Table: #__finder_links
--
CREATE TABLE "#__finder_links" (
  "link_id" serial NOT NULL,
  "url" varchar(255) NOT NULL,
  "route" varchar(255) NOT NULL,
  "title" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "indexdate" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "md5sum" varchar(32) DEFAULT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "state" integer DEFAULT 1,
  "access" integer DEFAULT 0,
  "language" varchar(8) DEFAULT '' NOT NULL,
  "publish_start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "list_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "sale_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "type_id" bigint NOT NULL,
  "object" bytea NOT NULL,
  PRIMARY KEY ("link_id")
);
CREATE INDEX "#__finder_links_idx_type" on "#__finder_links" ("type_id");
CREATE INDEX "#__finder_links_idx_title" on "#__finder_links" ("title");
CREATE INDEX "#__finder_links_idx_md5" on "#__finder_links" ("md5sum");
CREATE INDEX "#__finder_links_idx_url" on "#__finder_links" (substr(url,0,76));
CREATE INDEX "#__finder_links_idx_published_list" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "list_price");
CREATE INDEX "#__finder_links_idx_published_sale" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "sale_price");

--
-- Table: #__finder_links_terms0
--
CREATE TABLE "#__finder_links_terms0" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms0_idx_term_weight" on "#__finder_links_terms0" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms0_idx_link_term_weight" on "#__finder_links_terms0" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms1
--
CREATE TABLE "#__finder_links_terms1" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms1_idx_term_weight" on "#__finder_links_terms1" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms1_idx_link_term_weight" on "#__finder_links_terms1" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms2
--
CREATE TABLE "#__finder_links_terms2" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms2_idx_term_weight" on "#__finder_links_terms2" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms2_idx_link_term_weight" on "#__finder_links_terms2" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms3
--
CREATE TABLE "#__finder_links_terms3" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms3_idx_term_weight" on "#__finder_links_terms3" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms3_idx_link_term_weight" on "#__finder_links_terms3" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms4
--
CREATE TABLE "#__finder_links_terms4" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms4_idx_term_weight" on "#__finder_links_terms4" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms4_idx_link_term_weight" on "#__finder_links_terms4" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms5
--
CREATE TABLE "#__finder_links_terms5" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms5_idx_term_weight" on "#__finder_links_terms5" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms5_idx_link_term_weight" on "#__finder_links_terms5" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms6
--
CREATE TABLE "#__finder_links_terms6" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms6_idx_term_weight" on "#__finder_links_terms6" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms6_idx_link_term_weight" on "#__finder_links_terms6" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms7
--
CREATE TABLE "#__finder_links_terms7" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms7_idx_term_weight" on "#__finder_links_terms7" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms7_idx_link_term_weight" on "#__finder_links_terms7" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms8
--
CREATE TABLE "#__finder_links_terms8" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms8_idx_term_weight" on "#__finder_links_terms8" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms8_idx_link_term_weight" on "#__finder_links_terms8" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms9
--
CREATE TABLE "#__finder_links_terms9" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms9_idx_term_weight" on "#__finder_links_terms9" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms9_idx_link_term_weight" on "#__finder_links_terms9" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsa
--
CREATE TABLE "#__finder_links_termsa" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsa_idx_term_weight" on "#__finder_links_termsa" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsa_idx_link_term_weight" on "#__finder_links_termsa" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsb
--
CREATE TABLE "#__finder_links_termsb" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsb_idx_term_weight" on "#__finder_links_termsb" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsb_idx_link_term_weight" on "#__finder_links_termsb" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsc
--
CREATE TABLE "#__finder_links_termsc" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsc_idx_term_weight" on "#__finder_links_termsc" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsc_idx_link_term_weight" on "#__finder_links_termsc" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsd
--
CREATE TABLE "#__finder_links_termsd" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsd_idx_term_weight" on "#__finder_links_termsd" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsd_idx_link_term_weight" on "#__finder_links_termsd" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termse
--
CREATE TABLE "#__finder_links_termse" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termse_idx_term_weight" on "#__finder_links_termse" ("term_id", "weight");
CREATE INDEX "#__finder_links_termse_idx_link_term_weight" on "#__finder_links_termse" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsf
--
CREATE TABLE "#__finder_links_termsf" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsf_idx_term_weight" on "#__finder_links_termsf" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsf_idx_link_term_weight" on "#__finder_links_termsf" ("link_id", "term_id", "weight");

--
-- Table: #__finder_taxonomy
--
CREATE TABLE "#__finder_taxonomy" (
  "id" serial NOT NULL,
  "parent_id" integer DEFAULT 0 NOT NULL,
  "title" varchar(255) NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "access" smallint DEFAULT 0 NOT NULL,
  "ordering" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__finder_taxonomy_parent_id" on "#__finder_taxonomy" ("parent_id");
CREATE INDEX "#__finder_taxonomy_state" on "#__finder_taxonomy" ("state");
CREATE INDEX "#__finder_taxonomy_ordering" on "#__finder_taxonomy" ("ordering");
CREATE INDEX "#__finder_taxonomy_access" on "#__finder_taxonomy" ("access");
CREATE INDEX "#__finder_taxonomy_idx_parent_published" on "#__finder_taxonomy" ("parent_id", "state", "access");

--
-- Dumping data for table #__finder_taxonomy
--
INSERT INTO "#__finder_taxonomy" ("id", "parent_id", "title", "state", "access", "ordering") VALUES
(1, 0, 'ROOT', 0, 0, 0);

SELECT setval('#__finder_taxonomy_id_seq', 2, false);

--
-- Table: #__finder_taxonomy_map
--
CREATE TABLE "#__finder_taxonomy_map" (
  "link_id" integer NOT NULL,
  "node_id" integer NOT NULL,
  PRIMARY KEY ("link_id", "node_id")
);
CREATE INDEX "#__finder_taxonomy_map_link_id" on "#__finder_taxonomy_map" ("link_id");
CREATE INDEX "#__finder_taxonomy_map_node_id" on "#__finder_taxonomy_map" ("node_id");

--
-- Table: #__finder_terms
--
CREATE TABLE "#__finder_terms" (
  "term_id" serial NOT NULL,
  "term" varchar(75) NOT NULL,
  "stem" varchar(75) NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  "soundex" varchar(75) NOT NULL,
  "links" integer DEFAULT 0 NOT NULL,
  "language" varchar(3) NOT NULL,
  PRIMARY KEY ("term_id"),
  CONSTRAINT "#__finder_terms_idx_term" UNIQUE ("term")
);
CREATE INDEX "#__finder_terms_idx_term_phrase" on "#__finder_terms" ("term", "phrase");
CREATE INDEX "#__finder_terms_idx_stem_phrase" on "#__finder_terms" ("stem", "phrase");
CREATE INDEX "#__finder_terms_idx_soundex_phrase" on "#__finder_terms" ("soundex", "phrase");

--
-- Table: #__finder_terms_common
--
CREATE TABLE "#__finder_terms_common" (
  "term" varchar(75) NOT NULL,
  "language" varchar(3) DEFAULT '' NOT NULL
);
CREATE INDEX "#__finder_terms_common_idx_word_lang" on "#__finder_terms_common" ("term", "language");
CREATE INDEX "#__finder_terms_common_idx_lang" on "#__finder_terms_common" ("language");

--
-- Dumping data for table "#__finder_terms_common"
--

INSERT INTO "#__finder_terms_common" ("term", "language") VALUES
('a', 'en'),
('about', 'en'),
('after', 'en'),
('ago', 'en'),
('all', 'en'),
('am', 'en'),
('an', 'en'),
('and', 'en'),
('ani', 'en'),
('any', 'en'),
('are', 'en'),
('aren''t', 'en'),
('as', 'en'),
('at', 'en'),
('be', 'en'),
('but', 'en'),
('by', 'en'),
('for', 'en'),
('from', 'en'),
('get', 'en'),
('go', 'en'),
('how', 'en'),
('if', 'en'),
('in', 'en'),
('into', 'en'),
('is', 'en'),
('isn''t', 'en'),
('it', 'en'),
('its', 'en'),
('me', 'en'),
('more', 'en'),
('most', 'en'),
('must', 'en'),
('my', 'en'),
('new', 'en'),
('no', 'en'),
('none', 'en'),
('not', 'en'),
('noth', 'en'),
('nothing', 'en'),
('of', 'en'),
('off', 'en'),
('often', 'en'),
('old', 'en'),
('on', 'en'),
('onc', 'en'),
('once', 'en'),
('onli', 'en'),
('only', 'en'),
('or', 'en'),
('other', 'en'),
('our', 'en'),
('ours', 'en'),
('out', 'en'),
('over', 'en'),
('page', 'en'),
('she', 'en'),
('should', 'en'),
('small', 'en'),
('so', 'en'),
('some', 'en'),
('than', 'en'),
('thank', 'en'),
('that', 'en'),
('the', 'en'),
('their', 'en'),
('theirs', 'en'),
('them', 'en'),
('then', 'en'),
('there', 'en'),
('these', 'en'),
('they', 'en'),
('this', 'en'),
('those', 'en'),
('thus', 'en'),
('time', 'en'),
('times', 'en'),
('to', 'en'),
('too', 'en'),
('true', 'en'),
('under', 'en'),
('until', 'en'),
('up', 'en'),
('upon', 'en'),
('use', 'en'),
('user', 'en'),
('users', 'en'),
('veri', 'en'),
('version', 'en'),
('very', 'en'),
('via', 'en'),
('want', 'en'),
('was', 'en'),
('way', 'en'),
('were', 'en'),
('what', 'en'),
('when', 'en'),
('where', 'en'),
('whi', 'en'),
('which', 'en'),
('who', 'en'),
('whom', 'en'),
('whose', 'en'),
('why', 'en'),
('wide', 'en'),
('will', 'en'),
('with', 'en'),
('within', 'en'),
('without', 'en'),
('would', 'en'),
('yes', 'en'),
('yet', 'en'),
('you', 'en'),
('your', 'en'),
('yours', 'en');

--
-- Table: #__finder_tokens
--
CREATE TABLE "#__finder_tokens" (
  "term" varchar(75) NOT NULL,
  "stem" varchar(75) NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 1 NOT NULL,
  "context" smallint DEFAULT 2 NOT NULL,
  "language" varchar(3) NOT NULL
);
CREATE INDEX "#__finder_tokens_idx_word" on "#__finder_tokens" ("term");
CREATE INDEX "#__finder_tokens_idx_context" on "#__finder_tokens" ("context");

--
-- Table: #__finder_tokens_aggregate
--
CREATE TABLE "#__finder_tokens_aggregate" (
  "term_id" integer NOT NULL,
  "map_suffix" varchar(1) NOT NULL,
  "term" varchar(75) NOT NULL,
  "stem" varchar(75) NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "term_weight" numeric(8,2) NOT NULL,
  "context" smallint DEFAULT 2 NOT NULL,
  "context_weight" numeric(8,2) NOT NULL,
  "total_weight" numeric(8,2) NOT NULL,
  "language" varchar(3) NOT NULL
);
CREATE INDEX "#__finder_tokens_aggregate_token" on "#__finder_tokens_aggregate" ("term");
CREATE INDEX "_#__finder_tokens_aggregate_keyword_id" on "#__finder_tokens_aggregate" ("term_id");

--
-- Table: #__finder_types
--
CREATE TABLE "#__finder_types" (
  "id" serial NOT NULL,
  "title" varchar(100) NOT NULL,
  "mime" varchar(100) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__finder_types_title" UNIQUE ("title")
);

--
-- Table: #__languages
--
CREATE TABLE "#__languages" (
  "lang_id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "lang_code" varchar(7) NOT NULL,
  "title" varchar(50) NOT NULL,
  "title_native" varchar(50) NOT NULL,
  "sef" varchar(50) NOT NULL,
  "image" varchar(50) NOT NULL,
  "description" varchar(512) NOT NULL,
  "metakey" text NOT NULL,
  "metadesc" text NOT NULL,
  "sitename" varchar(1024) DEFAULT '' NOT NULL,
  "published" bigint DEFAULT 0 NOT NULL,
  "access" integer DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("lang_id"),
  CONSTRAINT "#__languages_idx_sef" UNIQUE ("sef"),
  CONSTRAINT "#__languages_idx_langcode" UNIQUE ("lang_code")
);
CREATE INDEX "#__languages_idx_ordering" ON "#__languages" ("ordering");
CREATE INDEX "#__languages_idx_access" ON "#__languages" ("access");

--
-- Dumping data for table #__languages
--
INSERT INTO "#__languages" ("lang_id", "lang_code", "title", "title_native", "sef", "image", "description", "metakey", "metadesc", "sitename", "published", "access", "ordering") VALUES
(1, 'en-GB', 'English (en-GB)', 'English (United Kingdom)', 'en', 'en_gb', '', '', '', '', 1, 1, 1);

SELECT setval('#__languages_lang_id_seq', 2, false);

--
-- Table: #__menu
--
CREATE TABLE "#__menu" (
  "id" serial NOT NULL,
  "menutype" varchar(24) NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "path" varchar(1024) DEFAULT '' NOT NULL,
  "link" varchar(1024) NOT NULL,
  "type" varchar(16) NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "parent_id" integer DEFAULT 1 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "component_id" integer DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "browserNav" smallint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "img" varchar(255) DEFAULT '' NOT NULL,
  "template_style_id" integer DEFAULT 0 NOT NULL,
  "params" text DEFAULT '' NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "home" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__menu_idx_client_id_parent_id_alias_language" UNIQUE ("client_id", "parent_id", "alias", "language")
);
CREATE INDEX "#__menu_idx_componentid" ON "#__menu" ("component_id", "menutype", "published", "access");
CREATE INDEX "#__menu_idx_menutype" ON "#__menu" ("menutype");
CREATE INDEX "#__menu_idx_left_right" ON "#__menu" ("lft", "rgt");
CREATE INDEX "#__menu_idx_alias" ON "#__menu" ("alias");
CREATE INDEX "#__menu_idx_path" ON "#__menu" ("path");
CREATE INDEX "#__menu_idx_language" ON "#__menu" ("language");

COMMENT ON COLUMN "#__menu"."menutype" IS 'The type of menu this item belongs to. FK to #__menu_types.menutype';
COMMENT ON COLUMN "#__menu"."title" IS 'The display title of the menu item.';
COMMENT ON COLUMN "#__menu"."alias" IS 'The SEF alias of the menu item.';
COMMENT ON COLUMN "#__menu"."path" IS 'The computed path of the menu item based on the alias field.';
COMMENT ON COLUMN "#__menu"."link" IS 'The actually link the menu item refers to.';
COMMENT ON COLUMN "#__menu"."type" IS 'The type of link: Component, URL, Alias, Separator';
COMMENT ON COLUMN "#__menu"."published" IS 'The published state of the menu link.';
COMMENT ON COLUMN "#__menu"."parent_id" IS 'The parent menu item in the menu tree.';
COMMENT ON COLUMN "#__menu"."level" IS 'The relative level in the tree.';
COMMENT ON COLUMN "#__menu"."component_id" IS 'FK to #__extensions.id';
COMMENT ON COLUMN "#__menu"."checked_out" IS 'FK to #__users.id';
COMMENT ON COLUMN "#__menu"."checked_out_time" IS 'The time the menu item was checked out.';
COMMENT ON COLUMN "#__menu"."browserNav" IS 'The click behaviour of the link.';
COMMENT ON COLUMN "#__menu"."access" IS 'The access level required to view the menu item.';
COMMENT ON COLUMN "#__menu"."img" IS 'The image of the menu item.';
COMMENT ON COLUMN "#__menu"."params" IS 'JSON encoded data for the menu item.';
COMMENT ON COLUMN "#__menu"."lft" IS 'Nested set lft.';
COMMENT ON COLUMN "#__menu"."rgt" IS 'Nested set rgt.';
COMMENT ON COLUMN "#__menu"."home" IS 'Indicates if this menu item is the home or default page.';

--
-- Dumping data for table #__menu
--
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "checked_out", "checked_out_time", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id") VALUES
(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '1970-01-01 00:00:00', 0, 0, '', 0, '', 0, 43, 0, '*', 0),
(2, 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 0, 1, 1, 4, 0, '1970-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 1, 10, 0, '*', 1),
(3, 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 0, 2, 2, 4, 0, '1970-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 2, 3, 0, '*', 1),
(4, 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 0, 2, 2, 6, 0, '1970-01-01 00:00:00', 0, 0, 'class:banners-cat', 0, '', 4, 5, 0, '*', 1),
(5, 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 0, 2, 2, 4, 0, '1970-01-01 00:00:00', 0, 0, 'class:banners-clients', 0, '', 6, 7, 0, '*', 1),
(6, 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 0, 2, 2, 4, 0, '1970-01-01 00:00:00', 0, 0, 'class:banners-tracks', 0, '', 8, 9, 0, '*', 1),
(7, 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 0, 1, 1, 8, 0, '1970-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 11, 16, 0, '*', 1),
(8, 'menu', 'com_contact_contacts', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 0, 7, 2, 8, 0, '1970-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 12, 13, 0, '*', 1),
(9, 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 0, 7, 2, 6, 0, '1970-01-01 00:00:00', 0, 0, 'class:contact-cat', 0, '', 14, 15, 0, '*', 1),
(10, 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 0, 1, 1, 15, 0, '1970-01-01 00:00:00', 0, 0, 'class:messages', 0, '', 17, 22, 0, '*', 1),
(11, 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 0, 10, 2, 15, 0, '1970-01-01 00:00:00', 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1),
(13, 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 1, 1, 17, 0, '1970-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 23, 28, 0, '*', 1),
(14, 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 13, 2, 17, 0, '1970-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 24, 25, 0, '*', 1),
(15, 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 0, 13, 2, 6, 0, '1970-01-01 00:00:00', 0, 0, 'class:newsfeeds-cat', 0, '', 26, 27, 0, '*', 1),
(16, 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 0, 1, 1, 24, 0, '1970-01-01 00:00:00', 0, 0, 'class:redirect', 0, '', 29, 30, 0, '*', 1),
(17, 'menu', 'com_search', 'Basic Search', '', 'Basic Search', 'index.php?option=com_search', 'component', 0, 1, 1, 19, 0, '1970-01-01 00:00:00', 0, 0, 'class:search', 0, '', 31, 32, 0, '*', 1),
(18, 'menu', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 0, 1, 1, 27, 0, '1970-01-01 00:00:00', 0, 0, 'class:finder', 0, '', 33, 34, 0, '*', 1),
(19, 'menu', 'com_joomlaupdate', 'Joomla! Update', '', 'Joomla! Update', 'index.php?option=com_joomlaupdate', 'component', 1, 1, 1, 28, 0, '1970-01-01 00:00:00', 0, 0, 'class:joomlaupdate', 0, '', 35, 36, 0, '*', 1),
(20, 'main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 0, 1, 1, 29, 0, '1970-01-01 00:00:00', 0, 1, 'class:tags', 0, '', 37, 38, 0, '', 1),
(21, 'main', 'com_postinstall', 'Post-installation messages', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', 0, 1, 1, 32, 0, '1970-01-01 00:00:00', 0, 1, 'class:postinstall', 0, '', 39, 40, 0, '*', 1),
(101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, 22, 0, '1970-01-01 00:00:00', 0, 1, '', 0, '{"featured_categories":[""],"layout_type":"blog","num_leading_articles":"1","num_intro_articles":"3","num_columns":"3","num_links":"0","multi_column_order":"1","orderby_pri":"","orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":1,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 41, 42, 1, '*', 0);

SELECT setval('#__menu_id_seq', 102, false);

--
-- Table: #__menu_types
--
CREATE TABLE "#__menu_types" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "menutype" varchar(24) NOT NULL,
  "title" varchar(48) NOT NULL,
  "description" varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__menu_types_idx_menutype" UNIQUE ("menutype")
);

--
-- Dumping data for table #__menu_types
--
INSERT INTO "#__menu_types" ("id", "menutype", "title", "description") VALUES
(1, 'mainmenu', 'Main Menu', 'The main menu for the site');

SELECT setval('#__menu_types_id_seq', 2, false);

--
-- Table: #__messages
--
CREATE TABLE "#__messages" (
  "message_id" serial NOT NULL,
  "user_id_from" bigint DEFAULT 0 NOT NULL,
  "user_id_to" bigint DEFAULT 0 NOT NULL,
  "folder_id" smallint DEFAULT 0 NOT NULL,
  "date_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "priority" smallint DEFAULT 0 NOT NULL,
  "subject" varchar(255) DEFAULT '' NOT NULL,
  "message" text NOT NULL,
  PRIMARY KEY ("message_id")
);
CREATE INDEX "#__messages_useridto_state" ON "#__messages" ("user_id_to", "state");

--
-- Table: #__messages_cfg
--
CREATE TABLE "#__messages_cfg" (
  "user_id" bigint DEFAULT 0 NOT NULL,
  "cfg_name" varchar(100) DEFAULT '' NOT NULL,
  "cfg_value" varchar(255) DEFAULT '' NOT NULL,
  CONSTRAINT "#__messages_cfg_idx_user_var_name" UNIQUE ("user_id", "cfg_name")
);

--
-- Table: #__modules
--
CREATE TABLE "#__modules" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(100) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "content" text DEFAULT '' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "position" varchar(50) DEFAULT '' NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "module" varchar(50) DEFAULT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "showtitle" smallint DEFAULT 1 NOT NULL,
  "params" text NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__modules_published" ON "#__modules" ("published", "access");
CREATE INDEX "#__modules_newsfeeds" ON "#__modules" ("module", "published");
CREATE INDEX "#__modules_idx_language" ON "#__modules" ("language");

--
-- Dumping data for table #__modules
--
INSERT INTO "#__modules" ("id", "asset_id", "title", "note", "content", "ordering", "position", "checked_out", "checked_out_time", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
(1, 39, 'Main Menu', '', '', 1, 'position-7', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_menu', 1, 1, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'),
(2, 40, 'Login', '', '', 1, 'login', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_login', 1, 1, '', 1, '*'),
(3, 41, 'Popular Articles', '', '', 3, 'cpanel', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(4, 42, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(8, 43, 'Toolbar', '', '', 1, 'toolbar', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_toolbar', 3, 1, '', 1, '*'),
(9, 44, 'Quick Icons', '', '', 1, 'icon', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_quickicon', 3, 1, '', 1, '*'),
(10, 45, 'Logged-in Users', '', '', 2, 'cpanel', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(12, 46, 'Admin Menu', '', '', 1, 'menu', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*'),
(13, 47, 'Admin Submenu', '', '', 1, 'submenu', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_submenu', 3, 1, '', 1, '*'),
(14, 48, 'User Status', '', '', 2, 'status', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_status', 3, 1, '', 1, '*'),
(15, 49, 'Title', '', '', 1, 'title', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_title', 3, 1, '', 1, '*'),
(16, 50, 'Login Form', '', '', 7, 'position-7', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*'),
(17, 51, 'Breadcrumbs', '', '', 1, 'position-2', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"0","cache_time":"0","cachemode":"itemid"}', 0, '*'),
(79, 52, 'Multilanguage status', '', '', 1, 'status', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(86, 53, 'Joomla Version', '', '', 1, 'footer', 0, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'mod_version', 3, 1, '{"format":"short","product":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*');

SELECT setval('#__modules_id_seq', 87, false);

--
-- Table: #__modules_menu
--
CREATE TABLE "#__modules_menu" (
  "moduleid" bigint DEFAULT 0 NOT NULL,
  "menuid" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("moduleid", "menuid")
);

--
-- Dumping data for table #__modules_menu
--
INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 0),
(6, 0),
(7, 0),
(8, 0),
(9, 0),
(10, 0),
(12, 0),
(13, 0),
(14, 0),
(15, 0),
(16, 0),
(17, 0),
(79, 0),
(86, 0);

--
-- Table: #__newsfeeds
--
CREATE TABLE "#__newsfeeds" (
  "catid" bigint DEFAULT 0 NOT NULL,
  "id" serial NOT NULL,
  "name" varchar(100) DEFAULT '' NOT NULL,
  "alias" varchar(100) DEFAULT '' NOT NULL,
  "link" varchar(2048) DEFAULT '' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "numarticles" bigint DEFAULT 1 NOT NULL,
  "cache_time" bigint DEFAULT 3600 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "rtl" smallint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "params" text NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" integer DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  "metakey" text NOT NULL,
  "metadesc" text NOT NULL,
  "metadata" text NOT NULL,
  "xreference" varchar(50) NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "description" text NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  "images" text NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__newsfeeds_idx_access" ON "#__newsfeeds" ("access");
CREATE INDEX "#__newsfeeds_idx_checkout" ON "#__newsfeeds" ("checked_out");
CREATE INDEX "#__newsfeeds_idx_state" ON "#__newsfeeds" ("published");
CREATE INDEX "#__newsfeeds_idx_catid" ON "#__newsfeeds" ("catid");
CREATE INDEX "#__newsfeeds_idx_createdby" ON "#__newsfeeds" ("created_by");
CREATE INDEX "#__newsfeeds_idx_language" ON "#__newsfeeds" ("language");
CREATE INDEX "#__newsfeeds_idx_xreference" ON "#__newsfeeds" ("xreference");

COMMENT ON COLUMN "#__newsfeeds"."xreference" IS 'A reference to enable linkages to external data sets.';

--
-- Table: #__overrider
--
CREATE TABLE "#__overrider" (
  "id" serial NOT NULL,
  "constant" varchar(255) NOT NULL,
  "string" text NOT NULL,
  "file" varchar(255) NOT NULL,
  PRIMARY KEY  ("id")
);

COMMENT ON COLUMN "#__overrider"."id" IS 'Primary Key';

--
-- Table: #__postinstall_messages
--
CREATE TABLE "#__postinstall_messages" (
  "postinstall_message_id" serial NOT NULL,
  "extension_id" bigint NOT NULL DEFAULT 700,
  "title_key" varchar(255) NOT NULL DEFAULT '',
  "description_key" varchar(255) NOT NULL DEFAULT '',
  "action_key" varchar(255) NOT NULL DEFAULT '',
  "language_extension" varchar(255) NOT NULL DEFAULT 'com_postinstall',
  "language_client_id" smallint NOT NULL DEFAULT 1,
  "type" varchar(10) NOT NULL DEFAULT 'link',
  "action_file" varchar(255) DEFAULT '',
  "action" varchar(255) DEFAULT '',
  "condition_file" varchar(255) DEFAULT NULL,
  "condition_method" varchar(255) DEFAULT NULL,
  "version_introduced" varchar(255) NOT NULL DEFAULT '3.2.0',
  "enabled" smallint NOT NULL DEFAULT 1,
  PRIMARY KEY ("postinstall_message_id")
);

COMMENT ON COLUMN "#__postinstall_messages"."extension_id" IS 'FK to jos_extensions';
COMMENT ON COLUMN "#__postinstall_messages"."title_key" IS 'Lang key for the title';
COMMENT ON COLUMN "#__postinstall_messages"."description_key" IS 'Lang key for description';
COMMENT ON COLUMN "#__postinstall_messages"."language_extension" IS 'Extension holding lang keys';
COMMENT ON COLUMN "#__postinstall_messages"."type" IS 'Message type - message, link, action';
COMMENT ON COLUMN "#__postinstall_messages"."action_file" IS 'RAD URI to the PHP file containing action method';
COMMENT ON COLUMN "#__postinstall_messages"."action" IS 'Action method name or URL';
COMMENT ON COLUMN "#__postinstall_messages"."condition_file" IS 'RAD URI to file holding display condition method';
COMMENT ON COLUMN "#__postinstall_messages"."condition_method" IS 'Display condition method, must return boolean';
COMMENT ON COLUMN "#__postinstall_messages"."version_introduced" IS 'Version when this message was introduced';

INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "action_key", "language_extension", "language_client_id", "type", "action_file", "action", "condition_file", "condition_method", "version_introduced", "enabled") VALUES
(700, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION', 'plg_twofactorauth_totp', 1, 'action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_condition', '3.2.0', 1),
(700, 'COM_CPANEL_WELCOME_BEGINNERS_TITLE', 'COM_CPANEL_WELCOME_BEGINNERS_MESSAGE', '', 'com_cpanel', 1, 'message', '', '', '', '', '3.2.0', 1),
(700, 'COM_CPANEL_MSG_STATS_COLLECTION_TITLE', 'COM_CPANEL_MSG_STATS_COLLECTION_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/statscollection.php', 'admin_postinstall_statscollection_condition', '3.5.0', 1),
(700, 'PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME', 'PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME_BODY', 'PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME_ACTION', 'plg_system_updatenotification', 1, 'action', 'site://plugins/system/updatenotification/postinstall/updatecachetime.php', 'updatecachetime_postinstall_action', 'site://plugins/system/updatenotification/postinstall/updatecachetime.php', 'updatecachetime_postinstall_condition', '3.6.3', 1),
(700, 'COM_CPANEL_MSG_JOOMLA40_PRE_CHECKS_TITLE', 'COM_CPANEL_MSG_JOOMLA40_PRE_CHECKS_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/joomla40checks.php', 'admin_postinstall_joomla40checks_condition', '3.7.0', 1);

--
-- Table: #__redirect_links
--
CREATE TABLE "#__redirect_links" (
  "id" serial NOT NULL,
  "old_url" varchar(2048) NOT NULL,
  "new_url" varchar(2048),
  "referer" varchar(2048) NOT NULL,
  "comment" varchar(255) NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  "published" smallint NOT NULL,
  "created_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "header" integer DEFAULT 301 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__redirect_links_idx_old_url" ON "#__redirect_links" ("old_url");
CREATE INDEX "#__redirect_links_idx_link_modifed" ON "#__redirect_links" ("modified_date");

--
-- Table: #__schemas
--
CREATE TABLE "#__schemas" (
  "extension_id" bigint NOT NULL,
  "version_id" varchar(20) NOT NULL,
  PRIMARY KEY ("extension_id", "version_id")
);

--
-- Table: #__session
--
CREATE TABLE "#__session" (
  "session_id" varchar(200) DEFAULT '' NOT NULL,
  "client_id" smallint DEFAULT NULL,
  "guest" smallint DEFAULT 1,
  "time" varchar(14) DEFAULT '',
  "data" text,
  "userid" bigint DEFAULT 0,
  "username" varchar(150) DEFAULT '',
  PRIMARY KEY ("session_id")
);
CREATE INDEX "#__session_userid" ON "#__session" ("userid");
CREATE INDEX "#__session_time" ON "#__session" ("time");

--
-- Table: #__tags
--
CREATE TABLE "#__tags" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text DEFAULT '' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "metadesc" varchar(1024) NOT NULL,
  "metakey" varchar(1024) NOT NULL,
  "metadata" varchar(2048) NOT NULL,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified_user_id" integer DEFAULT 0 NOT NULL,
  "modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "images" text NOT NULL,
  "urls" text NOT NULL,
  "hits" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
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
(1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 0, '1970-01-01 00:00:00', 1, '', '', '', '', 42, '2011-01-01 00:00:01', '', 0, '1970-01-01 00:00:00', '', '',  0, '*', 1);

SELECT setval('#__tags_id_seq', 2, false);

--
-- Table: #__template_styles
--
CREATE TABLE "#__template_styles" (
  "id" serial NOT NULL,
  "template" varchar(50) DEFAULT '' NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  "home" varchar(7) DEFAULT '0' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "params" text NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__template_styles_idx_template" ON "#__template_styles" ("template");
CREATE INDEX "#__template_styles_idx_home" ON "#__template_styles" ("home");

--
-- Dumping data for table #__template_styles
--

INSERT INTO "#__template_styles" ("id", "template", "client_id", "home", "title", "params") VALUES
(4, 'beez3', 0, '0', 'Beez3 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\\/joomla_black.png","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","templatecolor":"personal","html5":"0"}'),
(5, 'hathor', 1, '0', 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}'),
(7, 'protostar', 0, '1', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'),
(8, 'isis', 1, '1', 'isis - Default', '{"templateColor":"","logoFile":""}');

SELECT setval('#__template_styles_id_seq', 9, false);

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
  "core_type_alias" varchar(255) DEFAULT '' NOT NULL,
  "core_title" varchar(255) NOT NULL,
  "core_alias" varchar(255) DEFAULT '' NOT NULL,
  "core_body" text NOT NULL,
  "core_state" smallint DEFAULT 0 NOT NULL,
  "core_checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_checked_out_user_id" bigint DEFAULT 0 NOT NULL,
  "core_access" bigint DEFAULT 0 NOT NULL,
  "core_params" text NOT NULL,
  "core_featured" smallint DEFAULT 0 NOT NULL,
  "core_metadata" text NOT NULL,
  "core_created_user_id" bigint DEFAULT 0 NOT NULL,
  "core_created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "core_created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_modified_user_id" bigint DEFAULT 0 NOT NULL,
  "core_modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "core_language" varchar(7) DEFAULT '' NOT NULL,
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
  "core_xreference" varchar(50) DEFAULT '' NOT NULL,
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
-- Table: #__ucm_history
--
CREATE TABLE "#__ucm_history" (
  "version_id" serial NOT NULL,
  "ucm_item_id" integer NOT NULL,
  "ucm_type_id" integer NOT NULL,
  "version_note" varchar(255) NOT NULL DEFAULT '',
  "save_date" timestamp with time zone NOT NULL DEFAULT '1970-01-01 00:00:00',
  "editor_user_id" integer  NOT NULL DEFAULT 0,
  "character_count" integer  NOT NULL DEFAULT 0,
  "sha1_hash" varchar(50) NOT NULL DEFAULT '',
  "version_data" text NOT NULL,
  "keep_forever" smallint NOT NULL DEFAULT 0,
  PRIMARY KEY ("version_id")
);
CREATE INDEX "#__ucm_history_idx_ucm_item_id" ON "#__ucm_history" ("ucm_type_id", "ucm_item_id");
CREATE INDEX "#__ucm_history_idx_save_date" ON "#__ucm_history" ("save_date");

COMMENT ON COLUMN "#__ucm_history"."version_note" IS 'Optional version name';
COMMENT ON COLUMN "#__ucm_history"."character_count" IS 'Number of characters in this version.';
COMMENT ON COLUMN "#__ucm_history"."sha1_hash" IS 'SHA1 hash of the version_data column.';
COMMENT ON COLUMN "#__ucm_history"."version_data" IS 'json-encoded string of version data';
COMMENT ON COLUMN "#__ucm_history"."keep_forever" IS '0=auto delete; 1=keep';

--
-- Table: #__updates
--
CREATE TABLE "#__updates" (
  "update_id" serial NOT NULL,
  "update_site_id" bigint DEFAULT 0,
  "extension_id" bigint DEFAULT 0,
  "name" varchar(100) DEFAULT '',
  "description" text NOT NULL,
  "element" varchar(100) DEFAULT '',
  "type" varchar(20) DEFAULT '',
  "folder" varchar(20) DEFAULT '',
  "client_id" smallint DEFAULT 0,
  "version" varchar(32) DEFAULT '',
  "data" text DEFAULT '' NOT NULL,
  "detailsurl" text NOT NULL,
  "infourl" text NOT NULL,
  "extra_query" varchar(1000) DEFAULT '',
  PRIMARY KEY ("update_id")
);

COMMENT ON TABLE "#__updates" IS 'Available Updates';

--
-- Table: #__update_sites
--
CREATE TABLE "#__update_sites" (
  "update_site_id" serial NOT NULL,
  "name" varchar(100) DEFAULT '',
  "type" varchar(20) DEFAULT '',
  "location" text NOT NULL,
  "enabled" bigint DEFAULT 0,
  "last_check_timestamp" bigint DEFAULT 0,
  "extra_query" varchar(1000) DEFAULT '',
  PRIMARY KEY ("update_site_id")
);

COMMENT ON TABLE "#__update_sites" IS 'Update Sites';

--
-- Dumping data for table #__update_sites
--
INSERT INTO "#__update_sites" ("update_site_id", "name", "type", "location", "enabled", "last_check_timestamp") VALUES
(1, 'Joomla! Core', 'collection', 'https://update.joomla.org/core/list.xml', 1, 0),
(2, 'Accredited Joomla! Translations', 'collection', 'https://update.joomla.org/language/translationlist_3.xml', 1, 0),
(3, 'Joomla! Update Component Update Site', 'extension', 'https://update.joomla.org/core/extensions/com_joomlaupdate.xml', 1, 0);

SELECT setval('#__update_sites_update_site_id_seq', 4, false);

--
-- Table: #__update_sites_extensions
--
CREATE TABLE "#__update_sites_extensions" (
  "update_site_id" bigint DEFAULT 0 NOT NULL,
  "extension_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("update_site_id", "extension_id")
);

COMMENT ON TABLE "#__update_sites_extensions" IS 'Links extensions to update sites';

--
-- Dumping data for table #__update_sites_extensions
--
INSERT INTO "#__update_sites_extensions" ("update_site_id", "extension_id") VALUES
(1, 700),
(2, 802),
(3, 28);

--
-- Table: #__usergroups
--
CREATE TABLE "#__usergroups" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "title" varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__usergroups_idx_usergroup_parent_title_lookup" UNIQUE ("parent_id", "title")
);
CREATE INDEX "#__usergroups_idx_usergroup_title_lookup" ON "#__usergroups" ("title");
CREATE INDEX "#__usergroups_idx_usergroup_adjacency_lookup" ON "#__usergroups" ("parent_id");
CREATE INDEX "#__usergroups_idx_usergroup_nested_set_lookup" ON "#__usergroups" ("lft", "rgt");

COMMENT ON COLUMN "#__usergroups"."id" IS 'Primary Key';
COMMENT ON COLUMN "#__usergroups"."parent_id" IS 'Adjacency List Reference Id';
COMMENT ON COLUMN "#__usergroups"."lft" IS 'Nested set lft.';
COMMENT ON COLUMN "#__usergroups"."rgt" IS 'Nested set rgt.';

--
-- Dumping data for table #__usergroups
--
INSERT INTO "#__usergroups" ("id", "parent_id", "lft", "rgt", "title") VALUES
(1, 0, 1, 18, 'Public'),
(2, 1, 8, 15, 'Registered'),
(3, 2, 9, 14, 'Author'),
(4, 3, 10, 13, 'Editor'),
(5, 4, 11, 12, 'Publisher'),
(6, 1, 4, 7, 'Manager'),
(7, 6, 5, 6, 'Administrator'),
(8, 1, 16, 17, 'Super Users'),
(9, 1, 2, 3, 'Guest');

SELECT setval('#__usergroups_id_seq', 10, false);

--
-- Table: #__user_usergroup_map
--
CREATE TABLE "#__user_usergroup_map" (
  "user_id" bigint DEFAULT 0 NOT NULL,
  "group_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("user_id", "group_id")
);

COMMENT ON COLUMN "#__user_usergroup_map"."user_id" IS 'Foreign Key to #__users.id';
COMMENT ON COLUMN "#__user_usergroup_map"."group_id" IS 'Foreign Key to #__usergroups.id';

--
-- Table: #__users
--
CREATE TABLE "#__users" (
  "id" serial NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "username" varchar(150) DEFAULT '' NOT NULL,
  "email" varchar(100) DEFAULT '' NOT NULL,
  "password" varchar(100) DEFAULT '' NOT NULL,
  "block" smallint DEFAULT 0 NOT NULL,
  "sendEmail" smallint DEFAULT 0,
  "registerDate" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "lastvisitDate" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "activation" varchar(100) DEFAULT '' NOT NULL,
  "params" text NOT NULL,
  "lastResetTime" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "resetCount" bigint DEFAULT 0 NOT NULL,
  "otpKey" varchar(1000) DEFAULT '' NOT NULL,
  "otep" varchar(1000) DEFAULT '' NOT NULL,
  "requireReset" smallint DEFAULT 0,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__users_idx_name" ON "#__users" ("name");
CREATE INDEX "#__users_idx_block" ON "#__users" ("block");
CREATE INDEX "#__users_username" ON "#__users" ("username");
CREATE INDEX "#__users_email" ON "#__users" ("email");

COMMENT ON COLUMN "#__users"."lastResetTime" IS 'Date of last password reset';
COMMENT ON COLUMN "#__users"."resetCount" IS 'Count of password resets since lastResetTime';
COMMENT ON COLUMN "#__users"."requireReset" IS 'Require user to reset password on next login';

--
-- Table: #__user_keys
--
CREATE TABLE "#__user_keys" (
  "id" serial NOT NULL,
  "user_id" varchar(255) NOT NULL,
  "token" varchar(255) NOT NULL,
  "series" varchar(255) NOT NULL,
  "invalid" smallint NOT NULL,
  "time" varchar(200) NOT NULL,
  "uastring" varchar(255) NOT NULL,
  PRIMARY KEY ("id"),
	CONSTRAINT "#__user_keys_series" UNIQUE ("series"),
	CONSTRAINT "#__user_keys_series_2" UNIQUE ("series"),
	CONSTRAINT "#__user_keys_series_3" UNIQUE ("series")
);
CREATE INDEX "#__user_keys_idx_user_id" ON "#__user_keys" ("user_id");

--
-- Table: #__user_notes
--
CREATE TABLE "#__user_notes" (
  "id" serial NOT NULL,
  "user_id" integer DEFAULT 0 NOT NULL,
  "catid" integer DEFAULT 0 NOT NULL,
  "subject" varchar(100) DEFAULT '' NOT NULL,
  "body" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_user_id" integer NOT NULL,
  "modified_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "review_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__user_notes_idx_user_id" ON "#__user_notes" ("user_id");
CREATE INDEX "#__user_notes_idx_category_id" ON "#__user_notes" ("catid");

--
-- Table: #__user_profiles
--
CREATE TABLE "#__user_profiles" (
  "user_id" bigint NOT NULL,
  "profile_key" varchar(100) NOT NULL,
  "profile_value" text NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  CONSTRAINT "#__user_profiles_idx_user_id_profile_key" UNIQUE ("user_id", "profile_key")
);

COMMENT ON TABLE "#__user_profiles" IS 'Simple user profile storage table';

--
-- Table: #__viewlevels
--
CREATE TABLE "#__viewlevels" (
  "id" serial NOT NULL,
  "title" varchar(100) DEFAULT '' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "rules" varchar(5120) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__viewlevels_idx_assetgroup_title_lookup" UNIQUE ("title")
);

COMMENT ON COLUMN "#__viewlevels"."id" IS 'Primary Key';
COMMENT ON COLUMN "#__viewlevels"."rules" IS 'JSON encoded access control.';

--
-- Dumping data for table #__viewlevels
--
INSERT INTO "#__viewlevels" ("id", "title", "ordering", "rules") VALUES
(1, 'Public', 0, '[1]'),
(2, 'Registered', 2, '[6,2,8]'),
(3, 'Special', 3, '[6,3,8]'),
(5, 'Guest', 1, '[9]'),
(6, 'Super Users', 4, '[8]');

SELECT setval('#__viewlevels_id_seq', 7, false);

--
-- Here is SOUNDEX replacement for those who can't enable fuzzystrmatch module
--   from contrib folder.
-- This function comes from https://wiki.postgresql.org/wiki/Soundex
--   and is distributed with GPL license.
-- Thanks to its author, Marti Raudsepp, that published this piece of code.
--
CREATE OR REPLACE FUNCTION soundex(input text) RETURNS text
IMMUTABLE STRICT COST 500 LANGUAGE plpgsql
AS $$
DECLARE
  soundex text = '';
  char text;
  symbol text;
  last_symbol text = '';
  pos int = 1;
BEGIN
  WHILE length(soundex) < 4 LOOP
    char = upper(substr(input, pos, 1));
    pos = pos + 1;
    CASE char
    WHEN '' THEN
      -- End of input string
      IF soundex = '' THEN
        RETURN '';
      ELSE
        RETURN rpad(soundex, 4, '0');
      END IF;
    WHEN 'B', 'F', 'P', 'V' THEN
      symbol = '1';
    WHEN 'C', 'G', 'J', 'K', 'Q', 'S', 'X', 'Z' THEN
      symbol = '2';
    WHEN 'D', 'T' THEN
      symbol = '3';
    WHEN 'L' THEN
      symbol = '4';
    WHEN 'M', 'N' THEN
      symbol = '5';
    WHEN 'R' THEN
      symbol = '6';
    ELSE
      -- Not a consonant; no output, but next similar consonant will be re-recorded
      symbol = '';
    END CASE;

    IF soundex = '' THEN
      -- First character; only accept strictly English ASCII characters
      IF char ~>=~ 'A' AND char ~<=~ 'Z' THEN
        soundex = char;
        last_symbol = symbol;
      END IF;
    ELSIF last_symbol != symbol THEN
      soundex = soundex || symbol;
      last_symbol = symbol;
    END IF;
  END LOOP;

  RETURN soundex;
END;
$$;
