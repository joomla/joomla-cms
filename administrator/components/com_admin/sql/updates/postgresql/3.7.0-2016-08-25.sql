--
-- Table: #__user_logs
--
CREATE TABLE "#__user_logs" (
  "id" serial NOT NULL,
  "message" text NOT NULL DEFAULT '',
  "log_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "extension" varchar(50) NOT NULL DEFAULT '',
  "user_id" integer DEFAULT 0 NOT NULL,
  "ip_address" varchar(30) NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY ("id")
);

--
-- Table: #__user_logs_extensions
--
CREATE TABLE "#__user_logs_extensions" (
  "id" serial NOT NULL,
  "extension" varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY ("id")
);

--
-- Dumping data for table '#__user_logs_extensions'
--
INSERT INTO "#__user_logs_extensions" ("id", "extension") VALUES
(1, 'com_banners'),
(2, 'com_cache'),
(3, 'com_categories'),
(4, 'com_config'),
(5, 'com_contact'),
(6, 'com_content'),
(7, 'com_installer'),
(8, 'com_media'),
(9, 'com_menus'),
(10, 'com_messages'),
(11, 'com_modules'),
(12, 'com_newsfeeds'),
(13, 'com_plugins'),
(14, 'com_redirect'),
(15, 'com_tags'),
(16, 'com_templates'),
(17, 'com_users');
-- --------------------------------------------------------

--
-- Table: #__user_logs_tables_data
--
CREATE TABLE "#__user_logs_tables_data" (
  "id" serial NOT NULL,
  "type_title" varchar(255) NOT NULL DEFAULT '',
  "type_alias" varchar(255) NOT NULL DEFAULT '',
  "title_holder" varchar(255) NULL,
  "table_values" varchar(255) NULL,
  PRIMARY KEY ("id")
);

--
-- Dumping data for table #__user_logs_tables_data
--
INSERT INTO "#__user_logs_tables_data" ("id", "type_title", "type_alias", "title_holder", "table_values") VALUES
(1, 'article', 'com_content.article', 'title' ,'{"table_type":"Content","table_prefix":"JTable"}'),
(2, 'article', 'com_content.form', 'title' ,'{"table_type":"Content","table_prefix":"JTable"}'),
(3, 'banner', 'com_banners.banner', 'name' ,'{"table_type":"Banner","table_prefix":"BannersTable"}'),
(4, 'user_note', 'com_users.note', 'subject' ,'{"table_type":"Note","table_prefix":"UsersTable"}'),
(5, 'media', 'com_media.file', 'name' ,'{"table_type":"","table_prefix":""}'),
(6, 'category', 'com_categories.category', 'title' ,'{"table_type":"Category","table_prefix":"JTable"}'),
(7, 'menu', 'com_menus.menu', 'title' ,'{"table_type":"Menu","table_prefix":"JTable"}'),
(8, 'menu_item', 'com_menus.item', 'title' ,'{"table_type":"Menu","table_prefix":"JTable"}'),
(9, 'newsfeed', 'com_newsfeeds.newsfeed', 'name' ,'{"table_type":"Newsfeed","table_prefix":"NewsfeedsTable"}'),
(10, 'link', 'com_redirect.link', 'old_url' ,'{"table_type":"Link","table_prefix":"RedirectTable"}'),
(11, 'tag', 'com_tags.tag', 'title' ,'{"table_type":"Tag","table_prefix":"TagsTable"}'),
(12, 'style', 'com_templates.style', 'title' ,'{"table_type":"","table_prefix":""}'),
(13, 'plugin', 'com_plugins.plugin', 'name' ,'{"table_type":"Extension","table_prefix":"JTable"}'),
(14, 'component_config', 'com_config.component', 'name', '{"table_type":"","table_prefix":""}'),
(15, 'contact', 'com_contact.contact', 'name', '{"table_type":"Contact","table_prefix":"ContactTable"}'),
(16, 'module', 'com_modules.module', 'title', '{"table_type":"Module","table_prefix":"JTable"}'),
(17, 'access_level', 'com_users.level', 'title', '{"table_type":"Viewlevel","table_prefix":"JTable"}'),
(18, 'banner_client', 'com_banners.client', 'name', '{"table_type":"Client","table_prefix":"BannersTable"}');

INSERT INTO "#__extensions" ("extension_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(34, 'com_userlogs', 'component', 'com_userlogs', '', 1, 1, 1, 0, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(458, 'plg_system_userlogs', 'plugin', 'userlogs', 'system', 0, 0, 1, 0, '', '{"logDeletePeriod":"200","ip_logging":"1","loggable_extensions":["com_banners","com_cache","com_categories","com_config","com_contact","com_content","com_installer","com_media","com_menus","com_messages","com_modules","com_newsfeeds","com_plugins","com_redirect","com_tags","com_templates","com_users"]}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "checked_out", "checked_out_time", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id") VALUES
(22, 'main', 'com_userlogs', 'com-userlogs', '', 'com-userlogs', 'index.php?option=com_userlogs', 'component', 0, 1, 1, 34, 0, '1970-01-01 00:00:00', 0, 1, 'class:component', 0, '{}', 43, 44, 0, '*', 1);
