INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(36, 0, 'com_userlogs', 'component', 'com_userlogs', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(482, 0, 'plg_system_userlogs', 'plugin', 'userlogs', 'system', 0, 0, 1, 0, '', '{"logDeletePeriod":"0"}', '', '', 0, '1970-01-01 00:00:00', 0, 0),
(483, 0, 'plg_actionlogs_joomla', 'plugin', 'joomla', 'actionlogs', 0, 1, 1, 0, '', '{}', '', '', 0, '1970-01-01 00:00:00', 0, 0);

--
-- Table: #__user_logs
--
CREATE TABLE "#__user_logs" (
  "id" serial NOT NULL,
  `message_language_key` varchar(255) NOT NULL DEFAULT '',
  "message" text NOT NULL DEFAULT '',
  "log_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "extension" varchar(50) NOT NULL DEFAULT '',
  "user_id" integer DEFAULT 0 NOT NULL,
  "ip_address" varchar(30) NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY ("id")
);

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

SELECT setval('#__user_logs_extensions_id_seq', 19, false);
-- --------------------------------------------------------

--
-- Table: #__user_logs_tables_data
--
CREATE TABLE "#__user_logs_tables_data" (
  "id" serial NOT NULL,
  "type_title" varchar(255) NOT NULL DEFAULT '',
  "type_alias" varchar(255) NOT NULL DEFAULT '',
  "id_holder" varchar(255) NULL,
  "title_holder" varchar(255) NULL,
  "table_name" varchar(255) NULL,
  "text_prefix" varchar(255) NULL,
  PRIMARY KEY ("id")
);

--
-- Dumping data for table #__user_logs_tables_data
--
INSERT INTO "#__user_logs_tables_data" ("id", "type_title", "type_alias", "id_holder", "table_name", "text_prefix") VALUES
(1, 'article', 'com_content.article', 'id' ,'title' , '#__content', 'PLG_SYSTEM_USERLOGS'),
(2, 'article', 'com_content.form', 'id', 'title' , '#__content', 'PLG_SYSTEM_USERLOGS'),
(3, 'banner', 'com_banners.banner', 'id' ,'name' , '#__banners', 'PLG_SYSTEM_USERLOGS'),
(4, 'user_note', 'com_users.note', 'id', 'subject' ,'#__user_notes', 'PLG_SYSTEM_USERLOGS'),
(5, 'media', 'com_media.file', '' , 'name' , '',  'PLG_SYSTEM_USERLOGS'),
(6, 'category', 'com_categories.category', 'id' , 'title' , '#__categories', 'PLG_SYSTEM_USERLOGS'),
(7, 'menu', 'com_menus.menu', 'id' ,'title' , '#__menu_types', 'PLG_SYSTEM_USERLOGS'),
(8, 'menu_item', 'com_menus.item', 'id' , 'title' , '#__menu', 'PLG_SYSTEM_USERLOGS'),
(9, 'newsfeed', 'com_newsfeeds.newsfeed', 'id' ,'name' , '#__newsfeeds', 'PLG_SYSTEM_USERLOGS'),
(10, 'link', 'com_redirect.link', 'id', 'old_url' , '__redirect_links', 'PLG_SYSTEM_USERLOGS'),
(11, 'tag', 'com_tags.tag', 'id', 'title' , '#__tags', 'PLG_SYSTEM_USERLOGS'),
(12, 'style', 'com_templates.style', 'id' , 'title' , '#__template_styles', 'PLG_SYSTEM_USERLOGS'),
(13, 'plugin', 'com_plugins.plugin', 'extension_id' , 'name' , '#__extensions', 'PLG_SYSTEM_USERLOGS'),
(14, 'component_config', 'com_config.component', 'extension_id' , 'name', '', 'PLG_SYSTEM_USERLOGS'),
(15, 'contact', 'com_contact.contact', 'id', 'name', '#__contact_details', 'PLG_SYSTEM_USERLOGS'),
(16, 'module', 'com_modules.module', 'id' ,'title', '#__modules', 'PLG_SYSTEM_USERLOGS'),
(17, 'access_level', 'com_users.level', 'id' , 'title', '#__viewlevels', 'PLG_SYSTEM_USERLOGS'),
(18, 'banner_client', 'com_banners.client', 'id', 'name', '#__banner_clients', 'PLG_SYSTEM_USERLOGS');


SELECT setval('#__user_logs_tables_data_id_seq', 19, false);
