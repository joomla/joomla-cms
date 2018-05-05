/****** Object:  Table [#__user_logs] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_logs](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[message] [nvarchar](max) NOT NULL DEFAULT '',
	[log_date] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[extension] [nvarchar](255) NOT NULL DEFAULT '',
	[user_id] [bigint] NOT NULL DEFAULT 0,
	[ip_address] [nvarchar](30) NOT NULL DEFAULT '0.0.0.0',
	CONSTRAINT [PK_#__user_logs_id] PRIMARY KEY CLUSTERED
 (
 	[id] ASC
 )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
 ) ON [PRIMARY];

/****** Object:  Table [#__user_logs_extensions] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_logs_extensions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[extension] [nvarchar](255) NOT NULL DEFAULT '',
	CONSTRAINT [PK_#__user_logs_extensions_id] PRIMARY KEY CLUSTERED
 (
 	[id] ASC
 )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
 ) ON [PRIMARY];
SET IDENTITY_INSERT [#__user_logs_extensions]  ON;
INSERT INTO [#__user_logs_extensions] ([id], [extension])
SELECT 1, 'com_banners'
UNION ALL
SELECT 2, 'com_cache'
UNION ALL
SELECT 3, 'com_categories'
UNION ALL
SELECT 4, 'com_config'
UNION ALL
SELECT 5, 'com_contact'
UNION ALL
SELECT 6, 'com_content'
UNION ALL
SELECT 7, 'com_installer'
UNION ALL
SELECT 8, 'com_media'
UNION ALL
SELECT 9, 'com_menus'
UNION ALL
SELECT 10, 'com_messages'
UNION ALL
SELECT 11, 'com_modules'
UNION ALL
SELECT 12, 'com_newsfeeds'
UNION ALL
SELECT 13, 'com_plugins'
UNION ALL
SELECT 14, 'com_redirect'
UNION ALL
SELECT 15, 'com_tags'
UNION ALL
SELECT 16, 'com_templates'
UNION ALL
SELECT 17, 'com_users';

SET IDENTITY_INSERT [#__user_logs_extensions]  OFF;
/****** Object:  Table [#__user_logs_tables_data] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_logs_tables_data](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[type_title] [nvarchar](255) NOT NULL DEFAULT '',
	[type_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[title_holder] [nvarchar](255) NULL,
	[table_values] [nvarchar](255) NULL
	CONSTRAINT [PK_#__user_logs_tables_data_id] PRIMARY KEY CLUSTERED
 (
 	[id] ASC
 )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
 ) ON [PRIMARY];

SET IDENTITY_INSERT [#__user_logs_tables_data]  ON;

INSERT INTO [#__user_logs_tables_data] ([id], [type_title], [type_alias], [title_holder], [table_values])
SELECT 1, 'article', 'com_content.article', 'title' ,'{"table_type":"Content","table_prefix":"JTable"}'
UNION ALL
SELECT 2, 'article', 'com_content.form', 'title' ,'{"table_type":"Content","table_prefix":"JTable"}'
UNION ALL
SELECT 3, 'banner', 'com_banners.banner', 'name' ,'{"table_type":"Banner","table_prefix":"BannersTable"}'
UNION ALL
SELECT 4, 'user_note', 'com_users.note', 'subject' ,'{"table_type":"Note","table_prefix":"UsersTable"}'
UNION ALL
SELECT 5, 'media', 'com_media.file', 'name' ,'{"table_type":"","table_prefix":""}'
UNION ALL
SELECT 6, 'category', 'com_categories.category', 'title' ,'{"table_type":"Category","table_prefix":"JTable"}'
UNION ALL
SELECT 7, 'menu', 'com_menus.menu', 'title' ,'{"table_type":"Menu","table_prefix":"JTable"}'
UNION ALL
SELECT 8, 'menu_item', 'com_menus.item', 'title' ,'{"table_type":"Menu","table_prefix":"JTable"}'
UNION ALL
SELECT 9, 'newsfeed', 'com_newsfeeds.newsfeed', 'name' ,'{"table_type":"Newsfeed","table_prefix":"NewsfeedsTable"}'
UNION ALL
SELECT 10, 'link', 'com_redirect.link', 'old_url' ,'{"table_type":"Link","table_prefix":"RedirectTable"}'
UNION ALL
SELECT 11, 'tag', 'com_tags.tag', 'title' ,'{"table_type":"Tag","table_prefix":"TagsTable"}'
UNION ALL
SELECT 12, 'style', 'com_templates.style', 'title' ,'{"table_type":"","table_prefix":""}'
UNION ALL
SELECT 13, 'plugin', 'com_plugins.plugin', 'name' ,'{"table_type":"Extension","table_prefix":"JTable"}'
UNION ALL
SELECT 14, 'component_config', 'com_config.component', 'name', '{"table_type":"","table_prefix":""}'
UNION ALL
SELECT 15, 'contact', 'com_contact.contact', 'name', '{"table_type":"Contact","table_prefix":"ContactTable"}'
UNION ALL
SELECT 16, 'module', 'com_modules.module', 'title', '{"table_type":"Module","table_prefix":"JTable"}'
UNION ALL
SELECT 17, 'access_level', 'com_users.level', 'title', '{"table_type":"Viewlevel","table_prefix":"JTable"}'
UNION ALL
SELECT 18, 'banner_client', 'com_banners.client', 'name', '{"table_type":"Client","table_prefix":"BannersTable"}';

SET IDENTITY_INSERT [#__user_logs_tables_data]  OFF;
