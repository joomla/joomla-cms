/****** Object:  Table [#__assets] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__assets](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[parent_id] [int] NOT NULL DEFAULT 0,
	[lft] [int] NOT NULL DEFAULT 0,
	[rgt] [int] NOT NULL DEFAULT 0,
	[level] [bigint] NOT NULL,
	[name] [nvarchar](50) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[rules] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__assets_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__assets$idx_asset_name] UNIQUE NONCLUSTERED
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_lft_rgt] ON [#__assets]
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_parent_id] ON [#__assets]
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__assets] ON;

INSERT INTO [#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules])
SELECT 1, 0, 0, 103, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'
UNION ALL
SELECT 2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'
UNION ALL
SELECT 3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'
UNION ALL
SELECT 7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 8, 1, 17, 20, 1, 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.edit":{"4":1},"core.edit.state":{"5":1}}'
UNION ALL
SELECT 9, 1, 21, 22, 1, 'com_cpanel', 'com_cpanel', '{}'
UNION ALL
SELECT 10, 1, 23, 24, 1, 'com_installer', 'com_installer', '{"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'
UNION ALL
SELECT 11, 1, 25, 26, 1, 'com_languages', 'com_languages', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 12, 1, 27, 28, 1, 'com_login', 'com_login', '{}'
UNION ALL
SELECT 13, 1, 29, 30, 1, 'com_mailto', 'com_mailto', '{}'
UNION ALL
SELECT 14, 1, 31, 32, 1, 'com_massmail', 'com_massmail', '{}'
UNION ALL
SELECT 15, 1, 33, 34, 1, 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'
UNION ALL
SELECT 16, 1, 35, 38, 1, 'com_menus', 'com_menus', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 17, 1, 39, 40, 1, 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 18, 1, 41, 72, 1, 'com_modules', 'com_modules', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 19, 1, 73, 76, 1, 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 20, 1, 77, 78, 1, 'com_plugins', 'com_plugins', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 21, 1, 79, 80, 1, 'com_redirect', 'com_redirect', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 22, 1, 81, 82, 1, 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 23, 1, 83, 84, 1, 'com_templates', 'com_templates', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 24, 1, 85, 88, 1, 'com_users', 'com_users', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 26, 1, 89, 90, 1, 'com_wrapper', 'com_wrapper', '{}'
UNION ALL
SELECT 27, 8, 18, 19, 2, 'com_content.category.2', 'Uncategorised', '{}'
UNION ALL
SELECT 28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{}'
UNION ALL
SELECT 29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{}'
UNION ALL
SELECT 30, 19, 74, 75, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{}'
UNION ALL
SELECT 32, 24, 86, 87, 2, 'com_users.category.7', 'Uncategorised', '{}'
UNION ALL
SELECT 33, 1, 91, 92, 1, 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 34, 1, 93, 94, 1, 'com_joomlaupdate', 'com_joomlaupdate', '{}'
UNION ALL
SELECT 35, 1, 95, 96, 1, 'com_tags', 'com_tags', '{}'
UNION ALL
SELECT 36, 1, 97, 98, 1, 'com_contenthistory', 'com_contenthistory', '{}'
UNION ALL
SELECT 37, 1, 99, 100, 1, 'com_ajax', 'com_ajax', '{}'
UNION ALL
SELECT 38, 1, 101, 102, 1, 'com_postinstall', 'com_postinstall', '{}'
UNION ALL
SELECT 39, 18, 42, 43, 2, 'com_modules.module.1', 'Main Menu', '{}'
UNION ALL
SELECT 40, 18, 44, 45, 2, 'com_modules.module.2', 'Login', '{}'
UNION ALL
SELECT 41, 18, 46, 47, 2, 'com_modules.module.3', 'Popular Articles', '{}'
UNION ALL
SELECT 42, 18, 48, 49, 2, 'com_modules.module.4', 'Recently Added Articles', '{}'
UNION ALL
SELECT 43, 18, 50, 51, 2, 'com_modules.module.8', 'Toolbar', '{}'
UNION ALL
SELECT 44, 18, 52, 53, 2, 'com_modules.module.9', 'Quick Icons', '{}'
UNION ALL
SELECT 45, 18, 54, 55, 2, 'com_modules.module.10', 'Logged-in Users', '{}'
UNION ALL
SELECT 46, 18, 56, 57, 2, 'com_modules.module.12', 'Admin Menu', '{}'
UNION ALL
SELECT 47, 18, 58, 59, 2, 'com_modules.module.13', 'Admin Submenu', '{}'
UNION ALL
SELECT 48, 18, 60, 61, 2, 'com_modules.module.14', 'User Status', '{}'
UNION ALL
SELECT 49, 18, 62, 63, 2, 'com_modules.module.15', 'Title', '{}'
UNION ALL
SELECT 50, 18, 64, 65, 2, 'com_modules.module.16', 'Login Form', '{}'
UNION ALL
SELECT 51, 18, 66, 67, 2, 'com_modules.module.17', 'Breadcrumbs', '{}'
UNION ALL
SELECT 52, 18, 68, 69, 2, 'com_modules.module.79', 'Multilanguage status', '{}'
UNION ALL
SELECT 53, 18, 70, 71, 2, 'com_modules.module.86', 'Joomla Version', '{}'
UNION ALL
SELECT 54, 16, 36, 37, 2, 'com_menus.menu.1', 'Main Menu', '{}';

SET IDENTITY_INSERT [#__assets] OFF;

/****** Object:  Table [#__associations] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__associations](
	[id] [int] NOT NULL,
	[context] [nvarchar](50) NOT NULL,
	[key] [nchar](32) NOT NULL,
 CONSTRAINT [PK_#__associations_context] PRIMARY KEY CLUSTERED
(
	[context] ASC,
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__banner_clients] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__banner_clients](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL DEFAULT '',
	[contact] [nvarchar](255) NOT NULL DEFAULT '',
	[email] [nvarchar](255) NOT NULL DEFAULT '',
	[extrainfo] [nvarchar](max) NOT NULL,
	[state] [smallint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01 00:00:00',
	[metakey] [nvarchar](max) NOT NULL DEFAULT 0,
	[own_prefix] [smallint] NOT NULL DEFAULT 0,
	[metakey_prefix] [nvarchar](255) NOT NULL DEFAULT '',
	[purchase_type] [smallint] NOT NULL DEFAULT -1,
	[track_clicks] [smallint] NOT NULL DEFAULT -1,
	[track_impressions] [smallint] NOT NULL DEFAULT -1,
 CONSTRAINT [PK_#__banner_clients_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_metakey_prefix] ON [#__banner_clients]
(
	[metakey_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_own_prefix] ON [#__banner_clients]
(
	[own_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__banner_tracks] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__banner_tracks](
	[track_date] [datetime] NOT NULL,
	[track_type] [bigint] NOT NULL,
	[banner_id] [bigint] NOT NULL,
	[count] [bigint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__banner_tracks_track_date] PRIMARY KEY CLUSTERED
(
	[track_date] ASC,
	[track_type] ASC,
	[banner_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_banner_id] ON [#__banner_tracks]
(
	[banner_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_track_date] ON [#__banner_tracks]
(
	[track_date] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_track_type] ON [#__banner_tracks]
(
	[track_type] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__banners] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__banners](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[cid] [int] NOT NULL DEFAULT 0,
	[type] [int] NOT NULL DEFAULT 0,
	[name] [nvarchar](255) NOT NULL DEFAULT '',
	[alias] [nvarchar](255) NOT NULL DEFAULT '',
	[imptotal] [int] NOT NULL DEFAULT 0,
	[impmade] [int] NOT NULL DEFAULT 0,
	[clicks] [int] NOT NULL DEFAULT 0,
	[clickurl] [nvarchar](200) NOT NULL DEFAULT '',
	[state] [smallint] NOT NULL DEFAULT 0,
	[catid] [bigint] NOT NULL DEFAULT 0,
	[description] [nvarchar](max) NOT NULL,
	[custombannercode] [nvarchar](2048) NOT NULL,
	[sticky] [tinyint] NOT NULL DEFAULT 0,
	[ordering] [int] NOT NULL DEFAULT 0,
	[metakey] [nvarchar](max) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[own_prefix] [smallint] NOT NULL DEFAULT 0,
	[metakey_prefix] [nvarchar](255) NOT NULL DEFAULT '',
	[purchase_type] [smallint] NOT NULL DEFAULT -1,
	[track_clicks] [smallint] NOT NULL DEFAULT -1,
	[track_impressions] [smallint] NOT NULL DEFAULT -1,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[reset] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[language] [nvarchar](7) NOT NULL DEFAULT '',
	[created_by] [bigint] NOT NULL DEFAULT 0,
	[created_by_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[modified] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_by] [bigint] NOT NULL DEFAULT 0,
	[version] [bigint] NOT NULL DEFAULT 1,
 CONSTRAINT [PK_#__banners_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_banner_catid] ON [#__banners]
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__banners]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_metakey_prefix] ON [#__banners]
(
	[metakey_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_own_prefix] ON [#__banners]
(
	[own_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_state] ON [#__banners]
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__banners]
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__categories] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__categories](
	[id] [int] IDENTITY(1,1) NOT NULL ,
	[asset_id] [bigint] NOT NULL DEFAULT 0,
	[parent_id] [bigint] NOT NULL DEFAULT 0,
	[lft] [int] NOT NULL DEFAULT 0,
	[rgt] [int] NOT NULL DEFAULT 0,
	[level] [bigint] NOT NULL DEFAULT 0,
	[path] [nvarchar](255) NOT NULL DEFAULT '',
	[extension] [nvarchar](50) NOT NULL DEFAULT '',
	[title] [nvarchar](255) NOT NULL DEFAULT '',
	[alias] [nvarchar](255) NOT NULL DEFAULT '',
	[note] [nvarchar](255) NOT NULL DEFAULT '',
	[description] [nvarchar](max) NOT NULL DEFAULT '',
	[published] [smallint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[access] [int] NOT NULL DEFAULT 0,
	[params] [nvarchar](max) NOT NULL DEFAULT '',
	[metadesc] [nvarchar](1024) NOT NULL DEFAULT '',
	[metakey] [nvarchar](1024) NOT NULL DEFAULT '',
	[metadata] [nvarchar](2048) NOT NULL DEFAULT '',
	[created_user_id] [bigint] NOT NULL DEFAULT 0,
	[created_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_user_id] [bigint] NOT NULL DEFAULT 0,
	[modified_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[hits] [bigint] NOT NULL DEFAULT 0,
	[language] [nvarchar](7) NOT NULL DEFAULT '',
	[version] [bigint] NOT NULL DEFAULT 1,
 CONSTRAINT [PK_#__categories_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [cat_idx] ON [#__categories]
(
	[extension] ASC,
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_access] ON [#__categories]
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__categories]
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__categories]
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__categories]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__categories]
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_path] ON [#__categories]
(
	[path] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_created_user_id] ON [#__categories]
(
	[created_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_checked_out_time] ON [#__categories]
(
	[checked_out_time] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_asset_id] ON [#__categories]
(
	[asset_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__categories] ON;

INSERT INTO [#__categories] ([id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language], [version])
SELECT 1, 0, 0, 0, 11, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{}', '', '', '{}', 42, '2011-01-01 00:00:01', 0, '1900-01-01 00:00:00', 0, '*', 1
UNION ALL
SELECT 2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1900-01-01 00:00:00', 0, '*', 1
UNION ALL
SELECT 3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1900-01-01 00:00:00', 0, '*', 1
UNION ALL
SELECT 4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1900-01-01 00:00:00', 0, '*', 1
UNION ALL
SELECT 5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1900-01-01 00:00:00', 0, '*', 1
UNION ALL
SELECT 7, 32, 1, 9, 10, 1, 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2011-01-01 00:00:01', 0, '1900-01-01 00:00:00', 0, '*', 1;

SET IDENTITY_INSERT [#__categories] OFF;

/****** Object:  Table [#__contact_details] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__contact_details](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL DEFAULT '',
	[alias] [nvarchar](255) NOT NULL,
	[con_position] [nvarchar](255) NULL,
	[address] [nvarchar](max) NULL,
	[suburb] [nvarchar](100) NULL,
	[state] [nvarchar](100) NULL,
	[country] [nvarchar](100) NULL,
	[postcode] [nvarchar](100) NULL,
	[telephone] [nvarchar](255) NULL,
	[fax] [nvarchar](255) NULL,
	[misc] [nvarchar](max) NULL,
	[image] [nvarchar](255) NULL,
	[email_to] [nvarchar](255) NULL,
	[default_con] [tinyint] NOT NULL DEFAULT 0,
	[published] [smallint] NOT NULL,
	[checked_out] [int] NOT NULL,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[ordering] [int] NOT NULL DEFAULT 0,
	[params] [nvarchar](max) NOT NULL,
	[user_id] [int] NOT NULL DEFAULT 0,
	[catid] [int] NOT NULL DEFAULT 0,
	[access] [int] NOT NULL DEFAULT 0,
	[mobile] [nvarchar](255) NOT NULL DEFAULT '',
	[webpage] [nvarchar](255) NOT NULL DEFAULT '',
	[sortname1] [nvarchar](255) NOT NULL,
	[sortname2] [nvarchar](255) NOT NULL,
	[sortname3] [nvarchar](255) NOT NULL,
	[language] [nvarchar](7) NOT NULL,
	[created] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_by] [bigint] NOT NULL DEFAULT 0,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_by] [bigint] NOT NULL DEFAULT 0,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[featured] [tinyint] NOT NULL DEFAULT 0,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[version] [bigint] NOT NULL DEFAULT 1,
	[hits] [bigint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__contact_details_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_access] ON [#__contact_details]
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_catid] ON [#__contact_details]
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__contact_details]
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__contact_details]
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__contact_details]
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__contact_details]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_state] ON [#__contact_details]
(
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__contact_details]
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__content] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__content](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[asset_id] [bigint] NOT NULL DEFAULT 0,
	[title] [nvarchar](255) NOT NULL DEFAULT '',
	[alias] [nvarchar](255) NOT NULL,
	[introtext] [nvarchar](max) NOT NULL,
	[fulltext] [nvarchar](max) NOT NULL,
	[state] [smallint] NOT NULL DEFAULT 0,
	[catid] [bigint] NOT NULL DEFAULT 0,
	[created] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_by] [bigint] NOT NULL DEFAULT 0,
	[created_by_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[modified] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_by] [bigint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[images] [nvarchar](max) NOT NULL,
	[urls] [nvarchar](max) NOT NULL,
	[attribs] [nvarchar](max) NOT NULL,
	[version] [bigint] NOT NULL DEFAULT 1,
	[ordering] [int] NOT NULL DEFAULT 0,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[access] [bigint] NOT NULL DEFAULT 0,
	[hits] [bigint] NOT NULL DEFAULT 0,
	[metadata] [nvarchar](max) NOT NULL,
	[featured] [tinyint] NOT NULL DEFAULT 0,
	[language] [nvarchar](7) NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
 CONSTRAINT [PK_#__content_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_access] ON [#__content]
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_catid] ON [#__content]
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__content]
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__content]
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__content]
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__content]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_state] ON [#__content]
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__content]
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__content_frontpage] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__content_frontpage](
	[content_id] [int] NOT NULL DEFAULT 0,
	[ordering] [int] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__content_frontpage_content_id] PRIMARY KEY CLUSTERED
(
	[content_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__content_rating] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__content_rating](
	[content_id] [int] NOT NULL DEFAULT 0,
	[rating_sum] [bigint] NOT NULL DEFAULT 0,
	[rating_count] [bigint] NOT NULL DEFAULT 0,
	[lastip] [nvarchar](50) NOT NULL DEFAULT '',
 CONSTRAINT [PK_#__content_rating_content_id] PRIMARY KEY CLUSTERED
(
	[content_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__content_types] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__content_types](
	[type_id] [bigint] IDENTITY(10000,1) NOT NULL,
	[type_title] [nvarchar](255) NOT NULL DEFAULT '',
	[type_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[table] [nvarchar](255) NOT NULL DEFAULT '',
	[rules] [nvarchar](max) NOT NULL,
	[field_mappings] [nvarchar](max) NOT NULL,
	[router] [nvarchar](255) NOT NULL DEFAULT '',
	[content_history_options] [nvarchar](max) NULL,
 CONSTRAINT [PK_#__content_types_type_id] PRIMARY KEY CLUSTERED
(
	[type_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__content_types]
(
	[type_alias] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__content_types]  ON;

INSERT [#__content_types] ([type_id], [type_title], [type_alias], [table], [rules], [field_mappings], [router], [content_history_options])
SELECT 1, 'Article', 'com_content.article', '{"special":{"dbtable":"#__content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}, "special":{"fulltext":"fulltext"}}', 'ContentHelperRoute::getArticleRoute', '{"formFile":"administrator\/components\/com_content\/models\/forms\/article.xml", "hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'
UNION ALL
SELECT 2, 'Contact', 'com_contact.contact', '{"special":{"dbtable":"#__contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}}', 'ContactHelperRoute::getContactRoute', '{"formFile":"administrator\/components\/com_contact\/models\/forms\/contact.xml","hideFields":["default_con","checked_out","checked_out_time","version","xreference"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[ {"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ] }'
UNION ALL
SELECT 3, 'Newsfeed', 'com_newsfeeds.newsfeed', '{"special":{"dbtable":"#__newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}}', 'NewsfeedsHelperRoute::getNewsfeedRoute', '{"formFile":"administrator\/components\/com_newsfeeds\/models\/forms\/newsfeed.xml","hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'
UNION ALL
SELECT 4, 'User', 'com_users.user', '{"special":{"dbtable":"#__users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{}}', 'UsersHelperRoute::getUserRoute', ''
UNION ALL
SELECT 5, 'Article Category', 'com_content.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContentHelperRoute::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
UNION ALL
SELECT 6, 'Contact Category', 'com_contact.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContactHelperRoute::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
UNION ALL
SELECT 7, 'Newsfeeds Category', 'com_newsfeeds.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'NewsfeedsHelperRoute::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
UNION ALL
SELECT 8, 'Tag', 'com_tags.tag', '{"special":{"dbtable":"#__tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}}', 'TagsHelperRoute::getTagRoute', '{"formFile":"administrator\/components\/com_tags\/models\/forms\/tag.xml", "hideFields":["checked_out","checked_out_time","version", "lft", "rgt", "level", "path", "urls", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}'
UNION ALL
SELECT 9, 'Banner', 'com_banners.banner', '{"special":{"dbtable":"#__banners","key":"id","type":"Banner","prefix":"BannersTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"null","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"null"}, "special":{"imptotal":"imptotal", "impmade":"impmade", "clicks":"clicks", "clickurl":"clickurl", "custombannercode":"custombannercode", "cid":"cid", "purchase_type":"purchase_type", "track_impressions":"track_impressions", "track_clicks":"track_clicks"}}', '','{"formFile":"administrator\/components\/com_banners\/models\/forms\/banner.xml", "hideFields":["checked_out","checked_out_time","version", "reset"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "imptotal", "impmade", "reset"], "convertToInt":["publish_up", "publish_down", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"cid","targetTable":"#__banner_clients","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'
UNION ALL
SELECT 10, 'Banners Category', 'com_banners.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}','','{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
UNION ALL
SELECT 11, 'Banner Client', 'com_banners.client', '{"special":{"dbtable":"#__banner_clients","key":"id","type":"Client","prefix":"BannersTable"}}', '', '', '', '{"formFile":"administrator\/components\/com_banners\/models\/forms\/client.xml", "hideFields":["checked_out","checked_out_time"], "ignoreChanges":["checked_out", "checked_out_time"], "convertToInt":[], "displayLookup":[]}'
UNION ALL
SELECT 12, 'User Notes', 'com_users.note', '{"special":{"dbtable":"#__user_notes","key":"id","type":"Note","prefix":"UsersTable"}}', '', '', '', '{"formFile":"administrator\/components\/com_users\/models\/forms\/note.xml", "hideFields":["checked_out","checked_out_time", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}'
UNION ALL
SELECT 13, 'User Notes Category', 'com_users.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}';

SET IDENTITY_INSERT [#__content_types] OFF;

/****** Object:  Table [#__contentitem_tag_map] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__contentitem_tag_map](
	[type_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[core_content_id] [bigint] NOT NULL,
	[content_item_id] [int] NOT NULL,
	[tag_id] [bigint] NOT NULL,
	[tag_date] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[type_id] [int] NOT NULL,
 CONSTRAINT [#__contentitem_tag_map$uc_ItemnameTagid] UNIQUE NONCLUSTERED
(
	[type_id] ASC,
	[content_item_id] ASC,
	[tag_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_date_id] ON [#__contentitem_tag_map]
(
	[tag_date] ASC,
	[tag_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_content_id] ON [#__contentitem_tag_map]
(
	[core_content_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__core_log_searches] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__core_log_searches](
	[search_term] [nvarchar](128) NOT NULL DEFAULT '',
	[hits] [bigint] NOT NULL DEFAULT 0
) ON [PRIMARY];

/****** Object:  Table [#__extensions] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__extensions](
	[extension_id] [int] IDENTITY(10000,1) NOT NULL,
	[package_id] [bigint] NOT NULL DEFAULT 0,
	[name] [nvarchar](100) NOT NULL,
	[type] [nvarchar](20) NOT NULL,
	[element] [nvarchar](100) NOT NULL,
	[folder] [nvarchar](100) NOT NULL,
	[client_id] [smallint] NOT NULL,
	[enabled] [smallint] NOT NULL DEFAULT 0,
	[access] [int] NOT NULL DEFAULT 1,
	[protected] [smallint] NOT NULL DEFAULT 0,
	[manifest_cache] [nvarchar](max) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[custom_data] [nvarchar](max) NOT NULL,
	[system_data] [nvarchar](max) NOT NULL DEFAULT '',
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[ordering] [int] NULL DEFAULT 0,
	[state] [int] NULL DEFAULT 0,
 CONSTRAINT [PK_#__extensions_extension_id] PRIMARY KEY CLUSTERED
(
	[extension_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [element_clientid] ON [#__extensions]
(
	[element] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [element_folder_clientid] ON [#__extensions]
(
	[element] ASC,
	[folder] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [extension] ON [#__extensions]
(
	[type] ASC,
	[element] ASC,
	[folder] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__extensions] ON;

-- Components
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 1, 'com_mailto', 'component', 'com_mailto', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 2, 'com_wrapper', 'component', 'com_wrapper', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 3, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 4, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, '', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":"","save_history":"1","history_limit":10}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 5, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 6, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 7, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 8, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, '', '{"contact_layout":"_:default","show_contact_category":"hide","save_history":"1","history_limit":10,"show_contact_list":"0","presentation_style":"sliders","show_tags":"1","show_info":"1","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_image":"1","show_misc":"1","image":"","allow_vcard":"0","show_articles":"0","articles_display_num":"10","show_profile":"0","show_user_custom_fields":["-1"],"show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"0","maxLevel":"-1","show_subcat_desc":"1","show_empty_categories":"0","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_subcat_desc_cat":"1","show_empty_categories_cat":"0","show_cat_items_cat":"1","filter_field":"0","show_pagination_limit":"0","show_headings":"1","show_image_heading":"0","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_pagination":"2","show_pagination_results":"1","initial_sort":"ordering","captcha":"","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_feed_link":"1","sef_advanced":0,"sef_ids":0,"custom_fields_enable":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 9, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 10, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, '', '{"show_jed_info":"1","cachetimeout":"6","minimum_stability":"4"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 11, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, '', '{"administrator":"en-GB","site":"en-GB"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 12, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 13, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, '', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image\/jpeg,image\/gif,image\/png,image\/bmp,application\/x-shockwave-flash,application\/msword,application\/excel,application\/pdf,application\/powerpoint,text\/plain,application\/x-zip","upload_mime_illegal":"text\/html"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 14, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, '', '{"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 15, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 16, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 17, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, '', '{"newsfeed_layout":"_:default","save_history":"1","history_limit":5,"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_character_count":"0","feed_display_order":"des","float_first":"right","float_second":"right","show_tags":"1","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"-1","show_empty_categories":"0","show_subcat_desc":"1","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_items_cat":"1","filter_field":"1","show_pagination_limit":"1","show_headings":"1","show_articles":"0","show_link":"1","show_pagination":"1","show_pagination_results":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 18, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 19, 'com_search', 'component', 'com_search', '', 1, 1, 1, 0, '', '{"enabled":"0","search_phrases":"1","search_areas":"1","show_date":"1","opensearch_name":"","opensearch_description":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 20, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, '', '{"template_positions_display":"0","upload_limit":"10","image_formats":"gif,bmp,jpg,jpeg,png","source_formats":"txt,less,ini,xml,js,php,css,scss,sass","font_formats":"woff,ttf,otf","compressed_formats":"zip"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 22, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, '', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","show_publishing_options":"1","show_article_options":"1","save_history":"1","history_limit":10,"show_urls_images_frontend":"0","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_subcategory_content":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 23, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, '', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"NONE","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 24, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 25, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, '', '{"allowUserRegistration":"0","new_usertype":"2","guest_usergroup":"9","sendpassword":"1","useractivation":"2","mail_to_admin":"1","captcha":"","frontend_userparams":"1","site_language":"0","change_login_name":"0","reset_count":"10","reset_time":"1","minimum_length":"4","minimum_integers":"0","minimum_symbols":"0","minimum_uppercase":"0","save_history":"1","history_limit":5,"mailSubjectPrefix":"","mailBodySuffix":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 27, 'com_finder', 'component', 'com_finder', '', 1, 1, 0, 0, '', '{"show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_advanced":"1","expand_advanced":"0","show_date_filters":"0","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","memory_table_limit":30000,"title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stemmer":"snowball"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 28, 'com_joomlaupdate', 'component', 'com_joomlaupdate', '', 1, 1, 0, 1, '', '{"updatesource":"default","customurl":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 29, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 1, '', '{"tag_layout":"_:default","save_history":"1","history_limit":5,"show_tag_title":"0","tag_list_show_tag_image":"0","tag_list_show_tag_description":"0","tag_list_image":"","tag_list_orderby":"title","tag_list_orderby_direction":"ASC","show_headings":"0","tag_list_show_date":"0","tag_list_show_item_image":"0","tag_list_show_item_description":"0","tag_list_item_maximum_characters":0,"return_any_or_all":"1","include_children":"0","maximum":200,"tag_list_language_filter":"all","tags_layout":"_:default","all_tags_orderby":"title","all_tags_orderby_direction":"ASC","all_tags_show_tag_image":"0","all_tags_show_tag_descripion":"0","all_tags_tag_maximum_characters":20,"all_tags_show_tag_hits":"0","filter_field":"1","show_pagination_limit":"1","show_pagination":"2","show_pagination_results":"1","tag_field_ajax_mode":"1","show_feed_link":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 30, 'com_contenthistory', 'component', 'com_contenthistory', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 31, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 32, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 33, 'com_fields', 'component', 'com_fields', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 34, 'com_associations', 'component', 'com_associations', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

-- Libraries
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 103, 'Joomla! Platform', 'library', 'joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 104, 'IDNA Convert', 'library', 'idna_convert', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 105, 'FOF', 'library', 'fof', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 106, 'PHPass', 'library', 'phpass', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

-- Modules: Site
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 200, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 201, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 202, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 203, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 204, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 205, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 206, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 207, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 208, 'mod_login', 'module', 'mod_login', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 209, 'mod_menu', 'module', 'mod_menu', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 210, 'mod_articles_news', 'module', 'mod_articles_news', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 211, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 212, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 213, 'mod_search', 'module', 'mod_search', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 214, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 215, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 216, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 218, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 219, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 220, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 221, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 222, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 223, 'mod_finder', 'module', 'mod_finder', '', 0, 1, 0, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 316, 'mod_tags_popular', 'module', 'mod_tags_popular', '', 0, 1, 1, 0, '', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 317, 'mod_tags_similar', 'module', 'mod_tags_similar', '', 0, 1, 1, 0, '', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

-- Modules: Administrator
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 300, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 301, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 302, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 303, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 304, 'mod_login', 'module', 'mod_login', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 305, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 307, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 308, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 309, 'mod_status', 'module', 'mod_status', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 310, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 311, 'mod_title', 'module', 'mod_title', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 312, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 313, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, '', '{"cache":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 314, 'mod_version', 'module', 'mod_version', '', 1, 1, 1, 0, '', '{"format":"short","product":"1","cache":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 315, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, '', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

-- Plugins
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 400, 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 401, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 402, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 403, 'plg_content_contact', 'plugin', 'contact', 'content', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 404, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '', '{"mode":"1"}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 406, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '', '{"style":"xhtml"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 407, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 0, '', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 408, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 0, '', '{"position":"1"}', '', '', 0, '1900-01-01 00:00:00', 5, 0
UNION ALL
SELECT 409, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 6, 0
UNION ALL
SELECT 410, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '', '{"lineNumbers":"1","lineWrapping":"1","matchTags":"1","matchBrackets":"1","marker-gutter":"1","autoCloseTags":"1","autoCloseBrackets":"1","autoFocus":"1","theme":"default","tabmode":"indent"}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 411, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 412, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 0, '', '{"configuration":{"toolbars":{"2":{"toolbar1":["bold","underline","strikethrough","|","undo","redo","|","bullist","numlist","|","pastetext"]},"1":{"menu":["edit","insert","view","format","table","tools"],"toolbar1":["bold","italic","underline","strikethrough","|","alignleft","aligncenter","alignright","alignjustify","|","formatselect","|","bullist","numlist","|","outdent","indent","|","undo","redo","|","link","unlink","anchor","code","|","hr","table","|","subscript","superscript","|","charmap","pastetext","preview"]},"0":{"menu":["edit","insert","view","format","table","tools"],"toolbar1":["bold","italic","underline","strikethrough","|","alignleft","aligncenter","alignright","alignjustify","|","styleselect","|","formatselect","fontselect","fontsizeselect","|","searchreplace","|","bullist","numlist","|","outdent","indent","|","undo","redo","|","link","unlink","anchor","image","|","code","|","forecolor","backcolor","|","fullscreen","|","table","|","subscript","superscript","|","charmap","emoticons","media","hr","ltr","rtl","|","cut","copy","paste","pastetext","|","visualchars","visualblocks","nonbreaking","blockquote","template","|","print","preview","codesample","insertdatetime","removeformat"]}},"setoptions":{"2":{"access":["1"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"0","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""},"1":{"access":["6","2"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"0","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""},"0":{"access":["7","4","8"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"1","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""}}},"sets_amount":3,"html_height":"550","html_width":"750"}', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 413, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 414, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 415, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 416, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 417, 'plg_search_categories', 'plugin', 'categories', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 418, 'plg_search_contacts', 'plugin', 'contacts', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 419, 'plg_search_content', 'plugin', 'content', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 420, 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 422, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 423, 'plg_system_p3p', 'plugin', 'p3p', 'system', 0, 0, 1, 0, '', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 424, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 1, '', '{"browsercache":"0","cachetime":"15"}', '', '', 0, '1900-01-01 00:00:00', 9, 0
UNION ALL
SELECT 425, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 426, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 5, 0
UNION ALL
SELECT 427, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 0, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 6, 0
UNION ALL
SELECT 428, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 7, 0
UNION ALL
SELECT 429, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 8, 0
UNION ALL
SELECT 430, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 431, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 0, '', '{"autowebpage":"","category":"34","autopublish":"0"}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 432, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '', '{"autoregister":"1","mail_to_user":"1","forceLogout":"1"}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 433, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 0, '', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 434, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 436, 'plg_system_languagecode', 'plugin', 'languagecode', 'system', 0, 0, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 10, 0
UNION ALL
SELECT 437, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 438, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 439, 'plg_captcha_recaptcha', 'plugin', 'recaptcha', 'captcha', 0, 0, 1, 0, '', '{"public_key":"","private_key":"","theme":"clean"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 440, 'plg_system_highlight', 'plugin', 'highlight', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 7, 0
UNION ALL
SELECT 441, 'plg_content_finder', 'plugin', 'finder', 'content', 0, 0, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 442, 'plg_finder_categories', 'plugin', 'categories', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 443, 'plg_finder_contacts', 'plugin', 'contacts', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 444, 'plg_finder_content', 'plugin', 'content', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 445, 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 447, 'plg_finder_tags', 'plugin', 'tags', 'finder', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 448, 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 449, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 450, 'plg_twofactorauth_yubikey', 'plugin', 'yubikey', 'twofactorauth', 0, 0, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 451, 'plg_search_tags', 'plugin', 'tags', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","show_tagged_items":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 452, 'plg_system_updatenotification', 'plugin', 'updatenotification', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 453, 'plg_editors-xtd_module', 'plugin', 'module', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 454, 'plg_system_stats', 'plugin', 'stats', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 455, 'plg_installer_packageinstaller', 'plugin', 'packageinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 456, 'plg_installer_folderinstaller', 'plugin', 'folderinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 457, 'plg_installer_urlinstaller', 'plugin', 'urlinstaller', 'installer', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 458, 'plg_quickicon_phpversioncheck', 'plugin', 'phpversioncheck', 'quickicon', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 459, 'plg_editors-xtd_menu', 'plugin', 'menu', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 460, 'plg_editors-xtd_contact', 'plugin', 'contact', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 461, 'plg_system_fields', 'plugin', 'fields', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 462, 'plg_fields_calendar', 'plugin', 'calendar', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 463, 'plg_fields_checkboxes', 'plugin', 'checkboxes', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 464, 'plg_fields_color', 'plugin', 'color', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 465, 'plg_fields_editor', 'plugin', 'editor', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 466, 'plg_fields_gallery', 'plugin', 'gallery', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 467, 'plg_fields_imagelist', 'plugin', 'imagelist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 468, 'plg_fields_integer', 'plugin', 'integer', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 469, 'plg_fields_list', 'plugin', 'list', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 470, 'plg_fields_media', 'plugin', 'media', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 471, 'plg_fields_radio', 'plugin', 'radio', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 472, 'plg_fields_sql', 'plugin', 'sql', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 473, 'plg_fields_text', 'plugin', 'text', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 474, 'plg_fields_textarea', 'plugin', 'textarea', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 475, 'plg_fields_url', 'plugin', 'url', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 476, 'plg_fields_user', 'plugin', 'user', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 477, 'plg_fields_usergrouplist', 'plugin', 'usergrouplist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 478, 'plg_content_fields', 'plugin', 'fields', 'content', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 479, 'plg_editors-xtd_fields', 'plugin', 'fields', 'editors-xtd', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;
-- Templates
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 503, 'beez3', 'template', 'beez3', '', 0, 1, 1, 0, '', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, '', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 506, 'protostar', 'template', 'protostar', '', 0, 1, 1, 0, '', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 507, 'isis', 'template', 'isis', '', 1, 1, 1, 0, '', '{"templateColor":"","logoFile":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

-- Languages
INSERT INTO [#__extensions] ([extension_id], [package_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 600, 802, 'English (en-GB)', 'language', 'en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 601, 802, 'English (en-GB)', 'language', 'en-GB', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

-- Files Extensions
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 700, 'files_joomla', 'file', 'joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

-- Packages
INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 802, 'English (en-GB) Language Pack', 'package', 'pkg_en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT [#__extensions] OFF;

/****** Object:  Table [#__fields] ******/

SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__fields] (
	[id] [int] IDENTITY(1,1) NOT NULL,
	[asset_id] [int] NOT NULL DEFAULT 0,
	[context] [nvarchar](255) NOT NULL DEFAULT '',
	[group_id] [int] NOT NULL DEFAULT 0,
	[title] [nvarchar](255) NOT NULL DEFAULT '',
	[alias] [nvarchar](255) NOT NULL DEFAULT '',
	[label] [nvarchar](255) NOT NULL DEFAULT '',
	[default_value] [nvarchar](max) NOT NULL DEFAULT '',
	[type] [nvarchar](255) NOT NULL DEFAULT '',
	[note] [nvarchar](255) NOT NULL DEFAULT '',
	[description] [nvarchar](max) NOT NULL DEFAULT '',
	[state] [smallint] NOT NULL DEFAULT 0,
	[required] [smallint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01 00:00:00',
	[ordering] [int] NOT NULL DEFAULT 0,
	[params] [nvarchar](max) NOT NULL DEFAULT '',
	[fieldparams] [nvarchar](max) NOT NULL DEFAULT '',
	[language] [nvarchar](7) NOT NULL DEFAULT '',
	[created_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_user_id] [bigint] NOT NULL DEFAULT 0,
	[modified_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_by] [bigint] NOT NULL DEFAULT 0,
	[access] [int] NOT NULL DEFAULT 1,
CONSTRAINT [PK_#__fields_id] PRIMARY KEY CLUSTERED(
	[id] ASC)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__fields](
	[checked_out] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_state] ON [#__fields](
	[state] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_access] ON [#__fields](
	[access] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_context] ON [#__fields](
	[context] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__fields](
	[language] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__fields_categories] ******/

SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__fields_categories] ( 
	[field_id] [int] NOT NULL DEFAULT 0,
	[category_id] [int] NOT NULL DEFAULT 0,
CONSTRAINT [PK_#__fields_categories_id] PRIMARY KEY CLUSTERED(
	[field_id] ASC,
	[category_id] ASC)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]) ON [PRIMARY];

/****** Object:  Table [#__fields_groups] ******/

SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__fields_groups] (
	[id] [int] IDENTITY(1,1) NOT NULL,
	[asset_id] [int] NOT NULL DEFAULT 0,
	[context] [nvarchar](255) NOT NULL DEFAULT '',
	[title] [nvarchar](255) NOT NULL DEFAULT '',
	[note] [nvarchar](255) NOT NULL DEFAULT '',
	[description] [nvarchar](max) NOT NULL DEFAULT '',
	[state] [smallint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01 00:00:00',
	[ordering] [int] NOT NULL DEFAULT 0,
	[language] [nvarchar](7) NOT NULL DEFAULT '',
	[created] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_by] [bigint] NOT NULL DEFAULT 0,
	[modified] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_by] [bigint] NOT NULL DEFAULT 0,
	[access] [int] NOT NULL DEFAULT 1,
CONSTRAINT [PK_#__fields_groups_id] PRIMARY KEY CLUSTERED(
	[id] ASC)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON
 ) ON [PRIMARY]) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__fields_groups](
	[checked_out] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_state] ON [#__fields_groups](
	[state] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_created_by] ON [#__fields_groups](
	[created_by] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_access] ON [#__fields_groups](
	[access] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_context] ON [#__fields_groups](
	[context] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__fields_groups](
	[language] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__fields_values] ******/

SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__fields_values] (
	[field_id] [bigint] NOT NULL DEFAULT 1,
	[context] [nvarchar](255) NOT NULL DEFAULT '',
	[item_id] [nvarchar](255) NOT NULL DEFAULT '',
	[value] [nvarchar](max) NOT NULL DEFAULT '',
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_field_id] ON [#__fields_values](
	[field_id] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_context] ON [#__fields_values](
	[context] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_item_id] ON [#__fields_values](
	[item_id] ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_filters] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_filters](
	[filter_id] [bigint] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[state] [smallint] NOT NULL DEFAULT 1,
	[created] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_by] [bigint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[map_count] [bigint] NOT NULL DEFAULT 0,
	[data] [nvarchar](max) NOT NULL,
	[params] [nvarchar](max) NULL,
 CONSTRAINT [PK_#__finder_filters_filter_id] PRIMARY KEY CLUSTERED
(
	[filter_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
;

/****** Object:  Table [#__finder_links] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links](
	[link_id] [bigint] IDENTITY(1,1) NOT NULL,
	[url] [nvarchar](255) NOT NULL,
	[route] [nvarchar](255) NOT NULL,
	[title] [nvarchar](255) NULL,
	[description] [nvarchar](max) NULL,
	[indexdate] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[md5sum] [nvarchar](32) NULL,
	[published] [smallint] NOT NULL DEFAULT 1,
	[state] [int] NULL DEFAULT 1,
	[access] [int] NULL DEFAULT 0,
	[language] [nvarchar](8) NOT NULL,
	[publish_start_date] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_end_date] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[start_date] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[end_date] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[list_price] [float] NOT NULL DEFAULT 0,
	[sale_price] [float] NOT NULL DEFAULT 0,
	[type_id] [int] NOT NULL,
	[object] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__finder_links_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_title] ON [#__finder_links]
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_type] ON [#__finder_links]
(
	[type_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_url] ON [#__finder_links]
(
	[url] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms0] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms0](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms0_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms0]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms0]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms1] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms1](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms1_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms1]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms1]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms2] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms2](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms2_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms2]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms2]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms3] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms3](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms3_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms3]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms3]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms4] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms4](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms4_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms4]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms4]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms5] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms5](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms5_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms5]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms5]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms6] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms6](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms6_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms6]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms6]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms7] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms7](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms7_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms7]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms7]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms8] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms8](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms8_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms8]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms8]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_terms9] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms9](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms9_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms9]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms9]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_termsa] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_termsa](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsa_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsa]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsa]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_termsb] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_termsb](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsb_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsb]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsb]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_termsc] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_termsc](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsc_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsc]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsc]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_termsd] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_termsd](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsd_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsd]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsd]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_termse] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_termse](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termse_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termse]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termse]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_links_termsf] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_termsf](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsf_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsf]
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsf]
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_taxonomy] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_taxonomy](
	[id] [bigint] IDENTITY(2,1) NOT NULL,
	[parent_id] [bigint] NOT NULL DEFAULT 0,
	[title] [nvarchar](255) NOT NULL,
	[state] [tinyint] NOT NULL DEFAULT 1,
	[access] [tinyint] NOT NULL DEFAULT 0,
	[ordering] [tinyint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__finder_taxonomy_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [access] ON [#__finder_taxonomy]
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_parent_published] ON [#__finder_taxonomy]
(
	[parent_id] ASC,
	[state] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [ordering] ON [#__finder_taxonomy]
(
	[ordering] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [parent_id] ON [#__finder_taxonomy]
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [state] ON [#__finder_taxonomy]
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__finder_taxonomy] ON;

INSERT INTO [#__finder_taxonomy] ([id], [parent_id], [title], [state], [access], [ordering])
SELECT 1, 0, 'ROOT', 0, 0, 0;

SET IDENTITY_INSERT [#__finder_taxonomy] OFF;

/****** Object:  Table [#__finder_taxonomy_map] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_taxonomy_map](
	[link_id] [bigint] NOT NULL,
	[node_id] [bigint] NOT NULL,
 CONSTRAINT [PK_#__finder_taxonomy_map_link_id] PRIMARY KEY CLUSTERED
(
	[link_id] ASC,
	[node_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [link_id] ON [#__finder_taxonomy_map]
(
	[link_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [node_id] ON [#__finder_taxonomy_map]
(
	[node_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_terms] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_terms](
	[term_id] [bigint] IDENTITY(1,1) NOT NULL,
	[term] [nvarchar](75) NOT NULL,
	[stem] [nvarchar](75) NOT NULL,
	[common] [tinyint] NOT NULL DEFAULT 0,
	[phrase] [tinyint] NOT NULL DEFAULT 0,
	[weight] [real] NOT NULL DEFAULT 0,
	[soundex] [nvarchar](75) NOT NULL,
	[links] [int] NOT NULL DEFAULT 0,
	[language] [nvarchar](3) NOT NULL DEFAULT ''
 CONSTRAINT [PK_#__finder_terms_term_id] PRIMARY KEY CLUSTERED
(
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__finder_terms$idx_term] UNIQUE NONCLUSTERED
(
	[term] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_soundex_phrase] ON [#__finder_terms]
(
	[soundex] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_stem_phrase] ON [#__finder_terms]
(
	[stem] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_term_phrase] ON [#__finder_terms]
(
	[term] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_terms_common] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_terms_common](
	[term] [nvarchar](75) NOT NULL,
	[language] [nvarchar](3) NOT NULL,
 CONSTRAINT [PK_#__finder_terms_common] PRIMARY KEY CLUSTERED
(
	[term] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_lang] ON [#__finder_terms_common]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_word_lang] ON [#__finder_terms_common]
(
	[term] ASC,
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

INSERT INTO [#__finder_terms_common] ([term], [language])
SELECT 'a', 'en'
UNION ALL
SELECT 'about', 'en'
UNION ALL
SELECT 'after', 'en'
UNION ALL
SELECT 'ago', 'en'
UNION ALL
SELECT 'all', 'en'
UNION ALL
SELECT 'am', 'en'
UNION ALL
SELECT 'an', 'en'
UNION ALL
SELECT 'and', 'en'
UNION ALL
SELECT 'ani', 'en'
UNION ALL
SELECT 'any', 'en'
UNION ALL
SELECT 'are', 'en'
UNION ALL
SELECT 'aren''t', 'en'
UNION ALL
SELECT 'as', 'en'
UNION ALL
SELECT 'at', 'en'
UNION ALL
SELECT 'be', 'en'
UNION ALL
SELECT 'but', 'en'
UNION ALL
SELECT 'by', 'en'
UNION ALL
SELECT 'for', 'en'
UNION ALL
SELECT 'from', 'en'
UNION ALL
SELECT 'get', 'en'
UNION ALL
SELECT 'go', 'en'
UNION ALL
SELECT 'how', 'en'
UNION ALL
SELECT 'if', 'en'
UNION ALL
SELECT 'in', 'en'
UNION ALL
SELECT 'into', 'en'
UNION ALL
SELECT 'is', 'en'
UNION ALL
SELECT 'isn''t', 'en'
UNION ALL
SELECT 'it', 'en'
UNION ALL
SELECT 'its', 'en'
UNION ALL
SELECT 'me', 'en'
UNION ALL
SELECT 'more', 'en'
UNION ALL
SELECT 'most', 'en'
UNION ALL
SELECT 'must', 'en'
UNION ALL
SELECT 'my', 'en'
UNION ALL
SELECT 'new', 'en'
UNION ALL
SELECT 'no', 'en'
UNION ALL
SELECT 'none', 'en'
UNION ALL
SELECT 'not', 'en'
UNION ALL
SELECT 'noth', 'en'
UNION ALL
SELECT 'nothing', 'en'
UNION ALL
SELECT 'of', 'en'
UNION ALL
SELECT 'off', 'en'
UNION ALL
SELECT 'often', 'en'
UNION ALL
SELECT 'old', 'en'
UNION ALL
SELECT 'on', 'en'
UNION ALL
SELECT 'onc', 'en'
UNION ALL
SELECT 'once', 'en'
UNION ALL
SELECT 'onli', 'en'
UNION ALL
SELECT 'only', 'en'
UNION ALL
SELECT 'or', 'en'
UNION ALL
SELECT 'other', 'en'
UNION ALL
SELECT 'our', 'en'
UNION ALL
SELECT 'ours', 'en'
UNION ALL
SELECT 'out', 'en'
UNION ALL
SELECT 'over', 'en'
UNION ALL
SELECT 'page', 'en'
UNION ALL
SELECT 'she', 'en'
UNION ALL
SELECT 'should', 'en'
UNION ALL
SELECT 'small', 'en'
UNION ALL
SELECT 'so', 'en'
UNION ALL
SELECT 'some', 'en'
UNION ALL
SELECT 'than', 'en'
UNION ALL
SELECT 'thank', 'en'
UNION ALL
SELECT 'that', 'en'
UNION ALL
SELECT 'the', 'en'
UNION ALL
SELECT 'their', 'en'
UNION ALL
SELECT 'theirs', 'en'
UNION ALL
SELECT 'them', 'en'
UNION ALL
SELECT 'then', 'en'
UNION ALL
SELECT 'there', 'en'
UNION ALL
SELECT 'these', 'en'
UNION ALL
SELECT 'they', 'en'
UNION ALL
SELECT 'this', 'en'
UNION ALL
SELECT 'those', 'en'
UNION ALL
SELECT 'thus', 'en'
UNION ALL
SELECT 'time', 'en'
UNION ALL
SELECT 'times', 'en'
UNION ALL
SELECT 'to', 'en'
UNION ALL
SELECT 'too', 'en'
UNION ALL
SELECT 'true', 'en'
UNION ALL
SELECT 'under', 'en'
UNION ALL
SELECT 'until', 'en'
UNION ALL
SELECT 'up', 'en'
UNION ALL
SELECT 'upon', 'en'
UNION ALL
SELECT 'use', 'en'
UNION ALL
SELECT 'user', 'en'
UNION ALL
SELECT 'users', 'en'
UNION ALL
SELECT 'veri', 'en'
UNION ALL
SELECT 'version', 'en'
UNION ALL
SELECT 'very', 'en'
UNION ALL
SELECT 'via', 'en'
UNION ALL
SELECT 'want', 'en'
UNION ALL
SELECT 'was', 'en'
UNION ALL
SELECT 'way', 'en'
UNION ALL
SELECT 'were', 'en'
UNION ALL
SELECT 'what', 'en'
UNION ALL
SELECT 'when', 'en'
UNION ALL
SELECT 'where', 'en'
UNION ALL
SELECT 'whi', 'en'
UNION ALL
SELECT 'which', 'en'
UNION ALL
SELECT 'who', 'en'
UNION ALL
SELECT 'whom', 'en'
UNION ALL
SELECT 'whose', 'en'
UNION ALL
SELECT 'why', 'en'
UNION ALL
SELECT 'wide', 'en'
UNION ALL
SELECT 'will', 'en'
UNION ALL
SELECT 'with', 'en'
UNION ALL
SELECT 'within', 'en'
UNION ALL
SELECT 'without', 'en'
UNION ALL
SELECT 'would', 'en'
UNION ALL
SELECT 'yes', 'en'
UNION ALL
SELECT 'yet', 'en'
UNION ALL
SELECT 'you', 'en'
UNION ALL
SELECT 'your', 'en'
UNION ALL
SELECT 'yours', 'en';

/****** Object:  Table [#__finder_tokens] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_tokens](
	[term] [nvarchar](75) NOT NULL,
	[stem] [nvarchar](75) NOT NULL,
	[common] [tinyint] NOT NULL DEFAULT 0,
	[phrase] [tinyint] NOT NULL DEFAULT 0,
	[weight] [real] NOT NULL DEFAULT 0,
	[context] [tinyint] NOT NULL DEFAULT 2,
	[language] [nvarchar](3) NOT NULL DEFAULT ''
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_context] ON [#__finder_tokens]
(
	[context] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_word] ON [#__finder_tokens]
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_tokens_aggregate] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_tokens_aggregate](
	[term_id] [bigint] NOT NULL,
	[map_suffix] [nchar](1) NULL,
	[term] [nvarchar](75) NOT NULL,
	[stem] [nvarchar](75) NOT NULL,
	[common] [tinyint] NOT NULL DEFAULT 0,
	[phrase] [tinyint] NOT NULL DEFAULT 0,
	[term_weight] [real] NOT NULL,
	[context] [tinyint] NOT NULL DEFAULT 2,
	[context_weight] [real] NOT NULL,
	[total_weight] [real] NOT NULL,
	[language] [nvarchar](3) NOT NULL DEFAULT ''
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [keyword_id] ON [#__finder_tokens_aggregate]
(
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [token] ON [#__finder_tokens_aggregate]
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__finder_types] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_types](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[mime] [nvarchar](100) NOT NULL,
 CONSTRAINT [PK_#__finder_types_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__finder_types$title] UNIQUE NONCLUSTERED
(
	[title] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__languages] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__languages](
	[lang_id] [bigint] IDENTITY(1,1) NOT NULL,
	[asset_id] [bigint] NOT NULL DEFAULT 0,
	[lang_code] [nvarchar](7) NOT NULL,
	[title] [nvarchar](50) NOT NULL,
	[title_native] [nvarchar](50) NOT NULL,
	[sef] [nvarchar](50) NOT NULL,
	[image] [nvarchar](50) NOT NULL,
	[description] [nvarchar](512) NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[sitename] [nvarchar](1024) NOT NULL DEFAULT '',
	[published] [int] NOT NULL DEFAULT 0,
	[access] [bigint] NOT NULL DEFAULT 0,
	[ordering] [int] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__languages_lang_id] PRIMARY KEY CLUSTERED
(
	[lang_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__languages$idx_sef] UNIQUE NONCLUSTERED
(
	[sef] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE UNIQUE INDEX [idx_access] ON [#__languages]
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__languages] ON;

INSERT INTO [#__languages] ([lang_id], [lang_code], [title], [title_native], [sef], [image], [description], [metakey], [metadesc], [sitename], [published], [access], [ordering])
SELECT 1, 'en-GB', 'English (en-GB)', 'English (United Kingdom)', 'en', 'en_gb', '', '', '', '', 1, 1, 1;

SET IDENTITY_INSERT [#__languages] OFF;

/****** Object:  Table [#__menu] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__menu](
	[id] [int] IDENTITY(102,1) NOT NULL,
	[menutype] [nvarchar](24) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[note] [nvarchar](255) NOT NULL DEFAULT '',
	[path] [nvarchar](1024) NOT NULL,
	[link] [nvarchar](1024) NOT NULL,
	[type] [nvarchar](16) NOT NULL,
	[published] [smallint] NOT NULL DEFAULT 0,
	[parent_id] [bigint] NOT NULL DEFAULT 1,
	[level] [bigint] NOT NULL DEFAULT 0,
	[component_id] [bigint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[browserNav] [smallint] NOT NULL DEFAULT 0,
	[access] [int] NOT NULL DEFAULT 0,
	[inheritable] [tinyint] NOT NULL DEFAULT 1,
	[img] [nvarchar](255) NOT NULL,
	[template_style_id] [bigint] NOT NULL DEFAULT 0,
	[params] [nvarchar](max) NOT NULL,
	[lft] [int] NOT NULL DEFAULT 0,
	[rgt] [int] NOT NULL DEFAULT 0,
	[home] [tinyint] NOT NULL DEFAULT 0,
	[language] [nvarchar](7) NOT NULL DEFAULT '',
	[client_id] [smallint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__menu_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__menu$idx_client_id_parent_id_alias] UNIQUE NONCLUSTERED
(
	[client_id] ASC,
	[parent_id] ASC,
	[alias] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__menu]
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_componentid] ON [#__menu]
(
	[component_id] ASC,
	[menutype] ASC,
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__menu]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__menu]
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_menutype] ON [#__menu]
(
	[menutype] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_browserNav] ON [#__menu]
(
	[browserNav] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_home] ON [#__menu]
(
	[home] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_template_style_id] ON [#__menu]
(
	[template_style_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_img] ON [#__menu]
(
	[img] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__menu] ON;

INSERT INTO [#__menu] ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id])
SELECT 1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '1900-01-01 00:00:00', 0, 0, '', 0, '', 0, 45, 0, '*', 0
UNION ALL
SELECT 2, 'main', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 1, 1, 1, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 1, 10, 0, '*', 1
UNION ALL
SELECT 3, 'main', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 1, 2, 2, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 2, 3, 0, '*', 1
UNION ALL
SELECT 4, 'main', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 1, 2, 2, 6, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-cat', 0, '', 4, 5, 0, '*', 1
UNION ALL
SELECT 5, 'main', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 1, 2, 2, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-clients', 0, '', 6, 7, 0, '*', 1
UNION ALL
SELECT 6, 'main', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 1, 2, 2, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-tracks', 0, '', 8, 9, 0, '*', 1
UNION ALL
SELECT 7, 'main', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 1, 1, 1, 8, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 11, 16, 0, '*', 1
UNION ALL
SELECT 8, 'main', 'com_contact_contacts', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 1, 7, 2, 8, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 12, 13, 0, '*', 1
UNION ALL
SELECT 9, 'main', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 1, 7, 2, 6, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact-cat', 0, '', 14, 15, 0, '*', 1
UNION ALL
SELECT 10, 'main', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 1, 1, 1, 15, 0, '1900-01-01 00:00:00', 0, 0, 'class:messages', 0, '', 17, 22, 0, '*', 1
UNION ALL
SELECT 11, 'main', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 1, 10, 2, 15, 0, '1900-01-01 00:00:00', 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1
UNION ALL
SELECT 13, 'main', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 1, 1, 1, 17, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 23, 28, 0, '*', 1
UNION ALL
SELECT 14, 'main', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 1, 13, 2, 17, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 24, 25, 0, '*', 1
UNION ALL
SELECT 15, 'main', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 1, 13, 2, 6, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds-cat', 0, '', 26, 27, 0, '*', 1
UNION ALL
SELECT 16, 'main', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 1, 1, 1, 24, 0, '1900-01-01 00:00:00', 0, 0, 'class:redirect', 0, '', 29, 30, 0, '*', 1
UNION ALL
SELECT 17, 'main', 'com_search', 'Basic Search', '', 'Basic Search', 'index.php?option=com_search', 'component', 1, 1, 1, 19, 0, '1900-01-01 00:00:00', 0, 0, 'class:search', 0, '', 31, 32, 0, '*', 1
UNION ALL
SELECT 18, 'main', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 1, 1, 1, 27, 0, '1900-01-01 00:00:00', 0, 0, 'class:finder', 0, '', 33, 34, 0, '*', 1
UNION ALL
SELECT 19, 'main', 'com_joomlaupdate', 'Joomla! Update', '', 'Joomla! Update', 'index.php?option=com_joomlaupdate', 'component', 1, 1, 1, 28, 0, '1900-01-01 00:00:00', 0, 0, 'class:joomlaupdate', 0, '', 35, 36, 0, '*', 1
UNION ALL
SELECT 20, 'main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 1, 1, 1, 29, 0, '1900-01-01 00:00:00', 0, 1, 'class:tags', 0, '', 37, 38, 0, '*', 1
UNION ALL
SELECT 21, 'main', 'com_postinstall', 'Post-installation messages', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', 1, 1, 1, 32, 0, '1900-01-01 00:00:00', 0, 1, 'class:postinstall', 0, '', 39, 40, 0, '*', 1
UNION ALL
SELECT 22, 'main', 'com_associations', 'Multilingual Associations', '', 'Multilingual Associations', 'index.php?option=com_associations', 'component', 1, 1, 1, 34, 0, '1900-01-01 00:00:00', 0, 0, 'class:associations', 0, '', 41, 42, 0, '*', 1
UNION ALL
SELECT 101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, 22, 0, '1900-01-01 00:00:00', 0, 1, '', 0, '{"featured_categories":[""],"layout_type":"blog","num_leading_articles":"1","num_intro_articles":"3","num_columns":"3","num_links":"0","multi_column_order":"1","orderby_pri":"","orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":1,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 43, 44, 1, '*', 0;

SET IDENTITY_INSERT [#__menu] OFF;

/****** Object:  Table [#__menu_types] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__menu_types](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[asset_id] [bigint] NOT NULL DEFAULT 0,
	[menutype] [nvarchar](24) NOT NULL,
	[title] [nvarchar](48) NOT NULL,
	[description] [nvarchar](255) NOT NULL DEFAULT '',
	[client_id] [tinyint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__menu_types_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__menu_types$idx_menutype] UNIQUE NONCLUSTERED
(
	[menutype] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

SET IDENTITY_INSERT [#__menu_types] ON;

INSERT INTO [#__menu_types] ([id], [menutype], [title], [description], [client_id])
SELECT 1, 'mainmenu', 'Main Menu', 'The main menu for the site', 0;

SET IDENTITY_INSERT [#__menu_types] OFF;

/****** Object:  Table [#__messages] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__messages](
	[message_id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id_from] [bigint] NOT NULL DEFAULT 0,
	[user_id_to] [bigint] NOT NULL DEFAULT 0,
	[folder_id] [tinyint] NOT NULL DEFAULT 0,
	[date_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[state] [smallint] NOT NULL DEFAULT 0,
	[priority] [tinyint] NOT NULL,
	[subject] [nvarchar](255) NOT NULL DEFAULT '',
	[message] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__messages_message_id] PRIMARY KEY CLUSTERED
(
	[message_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [useridto_state] ON [#__messages]
(
	[user_id_to] ASC,
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__messages_cfg] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__messages_cfg](
	[user_id] [bigint] NOT NULL DEFAULT 0,
	[cfg_name] [nvarchar](100) NOT NULL DEFAULT '',
	[cfg_value] [nvarchar](255) NOT NULL DEFAULT '',
 CONSTRAINT [#__messages_cfg$idx_user_var_name] UNIQUE CLUSTERED
(
	[user_id] ASC,
	[cfg_name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__modules] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__modules](
	[id] [int] IDENTITY(87,1) NOT NULL,
  [asset_id] [bigint] NOT NULL DEFAULT 0,
	[title] [nvarchar](100) NOT NULL DEFAULT '',
	[note] [nvarchar](255) NOT NULL DEFAULT '',
	[content] [nvarchar](max) NOT NULL DEFAULT '',
	[ordering] [int] NOT NULL DEFAULT 0,
	[position] [nvarchar](50) NULL DEFAULT '',
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[published] [smallint] NOT NULL DEFAULT 0,
	[module] [nvarchar](50) NULL,
	[access] [bigint] NOT NULL DEFAULT 0,
	[showtitle] [tinyint] NOT NULL DEFAULT 1,
	[params] [nvarchar](max) NOT NULL,
	[client_id] [smallint] NOT NULL DEFAULT 0,
	[language] [nvarchar](7) NOT NULL,
 CONSTRAINT [PK_#__modules_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_language] ON [#__modules]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [newsfeeds] ON [#__modules]
(
	[module] ASC,
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [published] ON [#__modules]
(
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__modules] ON;

INSERT INTO [#__modules] ([id], [asset_id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language])
SELECT 1, 39, 'Main Menu', '', '', 1, 'position-7', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_menu', 1, 1, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 2, 40, 'Login', '', '', 1, 'login', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_login', 1, 1, '', 1, '*'
UNION ALL
SELECT 3, 41, 'Popular Articles', '', '', 3, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 4, 42, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 8, 43, 'Toolbar', '', '', 1, 'toolbar', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_toolbar', 3, 1, '', 1, '*'
UNION ALL
SELECT 9, 44, 'Quick Icons', '', '', 1, 'icon', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_quickicon', 3, 1, '', 1, '*'
UNION ALL
SELECT 10, 45, 'Logged-in Users', '', '', 2, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 12, 46, 'Admin Menu', '', '', 1, 'menu', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*'
UNION ALL
SELECT 13, 47, 'Admin Submenu', '', '', 1, 'submenu', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_submenu', 3, 1, '', 1, '*'
UNION ALL
SELECT 14, 48, 'User Status', '', '', 2, 'status', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_status', 3, 1, '', 1, '*'
UNION ALL
SELECT 15, 49, 'Title', '', '', 1, 'title', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_title', 3, 1, '', 1, '*'
UNION ALL
SELECT 16, 50, 'Login Form', '', '', 7, 'position-7', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*'
UNION ALL
SELECT 17, 51, 'Breadcrumbs', '', '', 1, 'position-2', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"0","cache_time":"0","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 79, 52, 'Multilanguage status', '', '', 1, 'status', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 86, 53, 'Joomla Version', '', '', 1, 'footer', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_version', 3, 1, '{"format":"short","product":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*';

SET IDENTITY_INSERT [#__modules] OFF;

/****** Object:  Table [#__modules_menu] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__modules_menu](
	[moduleid] [int] NOT NULL DEFAULT 0,
	[menuid] [int] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__modules_menu_moduleid] PRIMARY KEY CLUSTERED
(
	[moduleid] ASC,
	[menuid] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

INSERT INTO [#__modules_menu] ([moduleid], [menuid])
SELECT 1, 0
UNION ALL
SELECT 2, 0
UNION ALL
SELECT 3, 0
UNION ALL
SELECT 4, 0
UNION ALL
SELECT 6, 0
UNION ALL
SELECT 7, 0
UNION ALL
SELECT 8, 0
UNION ALL
SELECT 9, 0
UNION ALL
SELECT 10, 0
UNION ALL
SELECT 12, 0
UNION ALL
SELECT 13, 0
UNION ALL
SELECT 14, 0
UNION ALL
SELECT 15, 0
UNION ALL
SELECT 16, 0
UNION ALL
SELECT 17, 0
UNION ALL
SELECT 79, 0
UNION ALL
SELECT 86, 0;

/****** Object:  Table [#__newsfeeds] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__newsfeeds](
	[catid] [int] NOT NULL DEFAULT 0,
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL DEFAULT '',
	[alias] [nvarchar](255) NOT NULL,
	[link] [nvarchar](2048) NOT NULL DEFAULT '',
	[published] [smallint] NOT NULL DEFAULT 0,
	[numarticles] [bigint] NOT NULL DEFAULT 1,
	[cache_time] [bigint] NOT NULL DEFAULT 3600,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[ordering] [int] NOT NULL DEFAULT 0,
	[rtl] [smallint] NOT NULL DEFAULT 0,
	[access] [bigint] NOT NULL DEFAULT 0,
	[language] [nvarchar](7) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[created] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_by] [bigint] NOT NULL DEFAULT 0,
	[created_by_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[modified] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_by] [bigint] NOT NULL DEFAULT 0,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[description] [nvarchar](max) NOT NULL,
	[version] [bigint] NOT NULL DEFAULT 1,
	[hits] [bigint] NOT NULL DEFAULT 0,
	[images] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__newsfeeds_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_access] ON [#__newsfeeds]
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_catid] ON [#__newsfeeds]
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__newsfeeds]
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__newsfeeds]
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__newsfeeds]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_state] ON [#__newsfeeds]
(
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__newsfeeds]
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__overrider] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__overrider](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[constant] [nvarchar](255) NOT NULL,
	[string] [nvarchar](max) NOT NULL,
	[file] [nvarchar](255) NOT NULL,
 CONSTRAINT [PK_#__overrider_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__postinstall_messages] ******/

CREATE TABLE [#__postinstall_messages] (
	[postinstall_message_id] [bigint] IDENTITY(1,1) NOT NULL,
	[extension_id] [bigint] NOT NULL DEFAULT '700',
	[title_key] [nvarchar](255) NOT NULL DEFAULT '',
	[description_key] [nvarchar](255) NOT NULL DEFAULT '',
	[action_key] [nvarchar](255) NOT NULL DEFAULT '',
	[language_extension] [nvarchar](255) NOT NULL DEFAULT 'com_postinstall',
	[language_client_id] [int] NOT NULL DEFAULT 1,
	[type] [nvarchar](10) NOT NULL DEFAULT 'link',
	[action_file] [nvarchar](255) DEFAULT '',
	[action] [nvarchar](255) DEFAULT '',
	[condition_file] [nvarchar](255) DEFAULT NULL,
	[condition_method] [nvarchar](255) DEFAULT NULL,
	[version_introduced] [nvarchar](50) NOT NULL DEFAULT '3.2.0',
	[enabled] [int] NOT NULL DEFAULT 1,
 CONSTRAINT [PK_#__postinstall_message_id] PRIMARY KEY CLUSTERED
(
	[postinstall_message_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

INSERT INTO [#__postinstall_messages] ([extension_id], [title_key], [description_key], [action_key], [language_extension], [language_client_id], [type], [action_file], [action], [condition_file], [condition_method], [version_introduced], [enabled])
SELECT 700, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY', 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION', 'plg_twofactorauth_totp', 1, 'action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_action', 'site://plugins/twofactorauth/totp/postinstall/actions.php', 'twofactorauth_postinstall_condition', '3.2.0', 1
UNION ALL
SELECT 700, 'COM_CPANEL_WELCOME_BEGINNERS_TITLE', 'COM_CPANEL_WELCOME_BEGINNERS_MESSAGE', '', 'com_cpanel', 1, 'message', '', '', '', '', '3.2.0', 1
UNION ALL
SELECT 700, 'COM_CPANEL_MSG_STATS_COLLECTION_TITLE', 'COM_CPANEL_MSG_STATS_COLLECTION_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/statscollection.php', 'admin_postinstall_statscollection_condition', '3.5.0', 1
UNION ALL
SELECT 700, 'PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME', 'PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME_BODY', 'PLG_SYSTEM_UPDATENOTIFICATION_POSTINSTALL_UPDATECACHETIME_ACTION', 'plg_system_updatenotification', 1, 'action', 'site://plugins/system/updatenotification/postinstall/updatecachetime.php', 'updatecachetime_postinstall_action', 'site://plugins/system/updatenotification/postinstall/updatecachetime.php', 'updatecachetime_postinstall_condition', '3.6.3', 1
UNION ALL
SELECT 700, 'COM_CPANEL_MSG_JOOMLA40_PRE_CHECKS_TITLE', 'COM_CPANEL_MSG_JOOMLA40_PRE_CHECKS_BODY', '', 'com_cpanel', 1, 'message', '', '', 'admin://components/com_admin/postinstall/joomla40checks.php', 'admin_postinstall_joomla40checks_condition', '3.7.0', 1;

/****** Object:  Table [#__redirect_links] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__redirect_links](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[old_url] [nvarchar](2048) NOT NULL,
	[new_url] [nvarchar](2048),
	[referer] [nvarchar](2048) NOT NULL,
	[comment] [nvarchar](255) NOT NULL,
	[hits] [bigint] NOT NULL DEFAULT 0,
	[published] [smallint] NOT NULL,
	[created_date] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_date] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[header] [smallint] NOT NULL DEFAULT 301,
 CONSTRAINT [PK_#__redirect_links_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_old_url] ON [#__redirect_links]
(
	[old_url] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_link_modifed] ON [#__redirect_links]
(
	[modified_date] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__schemas] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__schemas](
	[extension_id] [int] NOT NULL,
	[version_id] [nvarchar](20) NOT NULL,
 CONSTRAINT [PK_#__schemas_extension_id] PRIMARY KEY CLUSTERED
(
	[extension_id] ASC,
	[version_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__session] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__session](
	[session_id] [nvarchar](200) NOT NULL DEFAULT '',
	[client_id] [tinyint] DEFAULT NULL,
	[guest] [tinyint] NULL DEFAULT 1,
	[time] [nvarchar](14) NULL DEFAULT '',
	[data] [nvarchar](max) NULL,
	[userid] [int] NULL DEFAULT 0,
	[username] [nvarchar](150) NULL DEFAULT '',
 CONSTRAINT [PK_#__session_session_id] PRIMARY KEY CLUSTERED
(
	[session_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [time] ON [#__session]
(
	[time] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [userid] ON [#__session]
(
	[userid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__tags] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__tags](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[parent_id] [bigint] NOT NULL DEFAULT 0,
	[lft] [int] NOT NULL DEFAULT 0,
	[rgt] [int] NOT NULL DEFAULT 0,
	[level] [bigint] NOT NULL DEFAULT 0,
	[path] [nvarchar](255) NOT NULL DEFAULT '',
	[title] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL DEFAULT '',
	[note] [nvarchar](255) NOT NULL DEFAULT '',
	[description] [nvarchar](max) NOT NULL,
	[published] [smallint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[access] [bigint] NOT NULL DEFAULT 0,
	[params] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](1024) NOT NULL,
	[metakey] [nvarchar](1024) NOT NULL,
	[metadata] [nvarchar](2048) NOT NULL,
	[created_user_id] [bigint] NOT NULL DEFAULT 0,
	[created_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_by_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[modified_user_id] [bigint] NOT NULL DEFAULT 0,
	[modified_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[images] [nvarchar](max) NOT NULL,
	[urls] [nvarchar](max) NOT NULL,
	[hits] [bigint] NOT NULL DEFAULT 0,
	[language] [nvarchar](7) NOT NULL,
	[version] [bigint] NOT NULL DEFAULT 1,
	[publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
 CONSTRAINT [PK_#__tags_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [tag_idx] ON [#__tags]
(
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_access] ON [#__tags]
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__tags]
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_path] ON [#__tags]
(
	[path] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__tags]
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__tags]
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__tags]
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__tags] ON;

INSERT INTO [#__tags] ([id], [parent_id], [lft], [rgt], [level], [path], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [created_by_alias], [modified_user_id], [modified_time], [images], [urls], [hits], [language], [version], [publish_up], [publish_down])
SELECT 1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 0, '1900-01-01 00:00:00', 1, '', '', '', '', 42, '2011-01-01 00:00:01', '', 0, '1900-01-01 00:00:00', '', '', 0, '*', 1, '1900-01-01 00:00:00', '1900-01-01 00:00:00';

SET IDENTITY_INSERT [#__tags] OFF;

/****** Object:  Table [#__template_styles] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__template_styles](
	[id] [bigint] IDENTITY(9,1) NOT NULL,
	[template] [nvarchar](50) NOT NULL DEFAULT '',
	[client_id] [tinyint] NOT NULL DEFAULT 0,
	[home] [nvarchar](7) NOT NULL DEFAULT 0,
	[title] [nvarchar](255) NOT NULL DEFAULT '',
	[params] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__template_styles_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_home] ON [#__template_styles]
(
	[home] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_template] ON [#__template_styles]
(
	[template] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__template_styles] ON;

INSERT [#__template_styles] ([id], [template], [client_id], [home], [title], [params])
SELECT 4, 'beez3', 0, '0', 'Beez3 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\/joomla_black.png","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","templatecolor":"personal","html5":"0"}'
UNION ALL
SELECT 5, 'hathor', 1, '0', 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}'
UNION ALL
SELECT 7, 'protostar', 0, '1', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'
UNION ALL
SELECT 8, 'isis', 1, '1', 'isis - Default', '{"templateColor":"","logoFile":""}';

SET IDENTITY_INSERT [#__template_styles] OFF;

/****** Object:  Table [#__ucm_base] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__ucm_base](
  [ucm_id] [bigint] NOT NULL,
  [ucm_item_id] [int] NOT NULL,
  [ucm_type_id] [int] NOT NULL,
  [ucm_language_id] [int] NOT NULL,
 CONSTRAINT [PK_#__ucm_base_ucm_id] PRIMARY KEY CLUSTERED
(
	[ucm_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [ucm_item_id] ON [#__ucm_base]
(
	[ucm_item_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [ucm_type_id] ON [#__ucm_base]
(
	[ucm_type_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [ucm_language_id] ON [#__ucm_base]
(
	[ucm_language_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__ucm_content] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__ucm_content](
	[core_content_id] [bigint] IDENTITY(1,1) NOT NULL,
	[core_type_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[core_title] [nvarchar](255) NOT NULL DEFAULT '',
	[core_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[core_body] [nvarchar](max) NOT NULL DEFAULT '',
	[core_state] [smallint] NOT NULL DEFAULT 0,
	[core_checked_out_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[core_checked_out_user_id] [bigint] NOT NULL DEFAULT 0,
	[core_access] [bigint] NOT NULL DEFAULT 0,
	[core_params] [nvarchar](max) NOT NULL DEFAULT '',
	[core_featured] [tinyint] NOT NULL DEFAULT 0,
	[core_metadata] [nvarchar](2048) NOT NULL DEFAULT '',
	[core_created_user_id] [bigint] NOT NULL DEFAULT 0,
	[core_created_by_alias] [nvarchar](255) NOT NULL DEFAULT '',
	[core_created_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[core_modified_user_id] [bigint] NOT NULL DEFAULT 0,
	[core_modified_time] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[core_language] [nvarchar](7) NOT NULL DEFAULT '',
	[core_publish_up] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[core_publish_down] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[core_content_item_id] [bigint] NOT NULL DEFAULT 0,
	[asset_id] [bigint] NOT NULL DEFAULT 0,
	[core_images] [nvarchar](max) NOT NULL DEFAULT '',
	[core_urls] [nvarchar](max) NOT NULL DEFAULT '',
	[core_hits] [bigint] NOT NULL DEFAULT 0,
	[core_version] [bigint] NOT NULL DEFAULT 1,
	[core_ordering] [int] NOT NULL DEFAULT 0,
	[core_metakey] [nvarchar](max) NOT NULL DEFAULT '',
	[core_metadesc] [nvarchar](max) NOT NULL DEFAULT '',
	[core_catid] [bigint] NOT NULL DEFAULT 0,
	[core_xreference] [nvarchar](50) NOT NULL DEFAULT '',
	[core_type_id] [bigint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__ucm_content_core_content_id] PRIMARY KEY CLUSTERED
(
	[core_content_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__ucm_content_core_content_id$idx_type_alias_item_id] UNIQUE NONCLUSTERED
(
	[core_type_alias] ASC,
	[core_content_item_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [tag_idx] ON [#__ucm_content]
(
	[core_state] ASC,
	[core_access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_access] ON [#__ucm_content]
(
	[core_access] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_alias] ON [#__ucm_content]
(
	[core_alias] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_language] ON [#__ucm_content]
(
	[core_language] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_title] ON [#__ucm_content]
(
	[core_title] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_modified_time] ON [#__ucm_content]
(
	[core_modified_time] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_created_time] ON [#__ucm_content]
(
	[core_created_time] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_content_type] ON [#__ucm_content]
(
	[core_type_alias] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_modified_user_id] ON [#__ucm_content]
(
	[core_modified_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_checked_out_user_id] ON [#__ucm_content]
(
	[core_checked_out_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_created_user_id] ON [#__ucm_content]
(
	[core_created_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_core_type_id] ON [#__ucm_content]
(
	[core_type_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__ucm_history] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__ucm_history](
	[version_id] [bigint] IDENTITY(1,1) NOT NULL,
	[ucm_item_id] [bigint] NOT NULL,
	[ucm_type_id] [bigint] NOT NULL,
	[version_note] [nvarchar](255) NOT NULL DEFAULT '',
	[save_date] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[editor_user_id] [bigint] NOT NULL DEFAULT 0,
	[character_count] [bigint] NOT NULL DEFAULT 0,
	[sha1_hash] [nvarchar](50) NOT NULL DEFAULT '',
	[version_data] [nvarchar](max) NOT NULL,
	[keep_forever] [smallint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__ucm_history_version_id] PRIMARY KEY CLUSTERED
(
	[version_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_ucm_item_id] ON [#__ucm_history]
(
	[ucm_type_id] ASC,
	[ucm_item_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_save_date] ON [#__ucm_history]
(
	[save_date] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__update_sites] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__update_sites](
	[update_site_id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NULL DEFAULT '',
	[type] [nvarchar](20) NULL DEFAULT '',
	[location] [nvarchar](max) NOT NULL,
	[enabled] [int] NULL DEFAULT 0,
	[last_check_timestamp] [int] NULL DEFAULT 0,
	[extra_query] [nvarchar](1000) NULL DEFAULT '',
 CONSTRAINT [PK_#__update_sites_update_site_id] PRIMARY KEY CLUSTERED
(
	[update_site_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

SET IDENTITY_INSERT [#__update_sites] ON;

INSERT INTO [#__update_sites] ([update_site_id], [name], [type], [location], [enabled], [last_check_timestamp])
SELECT 1, 'Joomla! Core', 'collection', 'https://update.joomla.org/core/list.xml', 1, 0
UNION ALL
SELECT 2, 'Accredited Joomla! Translations', 'collection', 'https://update.joomla.org/language/translationlist_3.xml', 1, 0
UNION ALL
SELECT 3, 'Joomla! Update Component Update Site', 'extension', 'https://update.joomla.org/core/extensions/com_joomlaupdate.xml', 1, 0;

SET IDENTITY_INSERT [#__update_sites] OFF;

/****** Object:  Table [#__update_sites_extensions] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__update_sites_extensions](
	[update_site_id] [int] NOT NULL DEFAULT 0,
	[extension_id] [int] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__update_sites_extensions_update_site_id] PRIMARY KEY CLUSTERED
(
	[update_site_id] ASC,
	[extension_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

INSERT INTO [#__update_sites_extensions] ([update_site_id], [extension_id])
SELECT 1, 700
UNION ALL
SELECT 2, 802
UNION ALL
SELECT 3, 28;

/****** Object:  Table [#__updates] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__updates](
	[update_id] [int] IDENTITY(1,1) NOT NULL,
	[update_site_id] [int] DEFAULT 0,
	[extension_id] [int] DEFAULT 0,
	[name] [nvarchar](100) DEFAULT '',
	[description] [nvarchar](max) NOT NULL,
	[element] [nvarchar](100) DEFAULT '',
	[type] [nvarchar](20) DEFAULT '',
	[folder] [nvarchar](20) DEFAULT '',
	[client_id] [smallint] DEFAULT 0,
	[version] [nvarchar](32) DEFAULT '',
	[data] [nvarchar](max) NOT NULL DEFAULT '',
	[detailsurl] [nvarchar](max) NOT NULL,
	[infourl] [nvarchar](max) NOT NULL,
	[extra_query] [nvarchar](1000) NULL DEFAULT '',
 CONSTRAINT [PK_#__updates_update_id] PRIMARY KEY CLUSTERED
(
	[update_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__user_keys] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_keys](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [nvarchar](255) NOT NULL,
	[token] [nvarchar](255) NOT NULL,
	[series] [nvarchar](255) NOT NULL,
	[invalid] [smallint] NOT NULL,
	[time] [nvarchar](200) NOT NULL,
	[uastring] [nvarchar](255) NOT NULL,
 CONSTRAINT [PK_#__user_keys_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__user_keys$series] UNIQUE NONCLUSTERED
(
	[series] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__user_keys$series_2] UNIQUE NONCLUSTERED
(
	[series] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__user_keys$series_3] UNIQUE NONCLUSTERED
(
	[series] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [user_id] ON [#__user_keys]
(
  [user_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__user_notes] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_notes](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [bigint] NOT NULL DEFAULT 0,
	[catid] [bigint] NOT NULL DEFAULT 0,
	[subject] [nvarchar](100) NOT NULL DEFAULT '',
	[body] [nvarchar](max) NOT NULL,
	[state] [smallint] NOT NULL DEFAULT 0,
	[checked_out] [bigint] NOT NULL DEFAULT 0,
	[checked_out_time] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[created_user_id] [bigint] NOT NULL,
	[created_time] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[modified_user_id] [bigint] NOT NULL,
	[modified_time] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[review_time] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_up] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[publish_down] [datetime2](0) NOT NULL DEFAULT '1900-01-01T00:00:00.000',
 CONSTRAINT [PK_#__user_notes_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_category_id] ON [#__user_notes]
(
 [catid] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_user_id] ON [#__user_notes]
(
 [user_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__user_profiles] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_profiles](
	[user_id] [int] NOT NULL,
	[profile_key] [nvarchar](100) NOT NULL,
	[profile_value] [nvarchar](max) NOT NULL,
	[ordering] [int] NOT NULL DEFAULT 0,
 CONSTRAINT [#__user_profiles$idx_user_id_profile_key] UNIQUE CLUSTERED
(
	[user_id] ASC,
	[profile_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__user_usergroup_map] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_usergroup_map](
	[user_id] [bigint] NOT NULL DEFAULT 0,
	[group_id] [bigint] NOT NULL DEFAULT 0,
 CONSTRAINT [PK_#__user_usergroup_map_user_id] PRIMARY KEY CLUSTERED
(
	[user_id] ASC,
	[group_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

/****** Object:  Table [#__usergroups] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__usergroups](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[parent_id] [bigint] NOT NULL DEFAULT 0,
	[lft] [int] NOT NULL DEFAULT 0,
	[rgt] [int] NOT NULL DEFAULT 0,
	[title] [nvarchar](100) NOT NULL DEFAULT '',
 CONSTRAINT [PK_#__usergroups_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__usergroups$idx_usergroup_parent_title_lookup] UNIQUE NONCLUSTERED
(
	[title] ASC,
	[parent_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [idx_usergroup_title_lookup] ON [#__usergroups]
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_usergroup_adjacency_lookup] ON [#__usergroups]
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_usergroup_nested_set_lookup] ON [#__usergroups]
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

SET IDENTITY_INSERT [#__usergroups] ON;

INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title])
SELECT 1, 0, 1, 18, 'Public'
UNION ALL
SELECT 2, 1, 8, 15, 'Registered'
UNION ALL
SELECT 3, 2, 9, 14, 'Author'
UNION ALL
SELECT 4, 3, 10, 13, 'Editor'
UNION ALL
SELECT 5, 4, 11, 12, 'Publisher'
UNION ALL
SELECT 6, 1, 4, 7, 'Manager'
UNION ALL
SELECT 7, 6, 5, 6, 'Administrator'
UNION ALL
SELECT 8, 1, 16, 17, 'Super Users'
UNION ALL
SELECT 9, 1, 2, 3, 'Guest';

SET IDENTITY_INSERT [#__usergroups] OFF;

/****** Object:  Table [#__users] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL DEFAULT '',
	[username] [nvarchar](150) NOT NULL DEFAULT '',
	[email] [nvarchar](100) NOT NULL DEFAULT '',
	[password] [nvarchar](100) NOT NULL DEFAULT '',
	[block] [smallint] NOT NULL DEFAULT 0,
	[sendEmail] [smallint] NULL DEFAULT 0,
	[registerDate] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[lastvisitDate] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[activation] [nvarchar](100) NOT NULL DEFAULT '',
	[params] [nvarchar](max) NOT NULL,
	[lastResetTime] [datetime] NOT NULL DEFAULT '1900-01-01T00:00:00.000',
	[resetCount] [int] NOT NULL DEFAULT 0,
	[otpKey] [nvarchar](1000) NOT NULL DEFAULT '',
	[otep] [nvarchar](1000) NOT NULL DEFAULT '',
	[requireReset] [smallint] NULL DEFAULT 0,
 CONSTRAINT [PK_#__users_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE NONCLUSTERED INDEX [email] ON [#__users]
(
	[email] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_block] ON [#__users]
(
	[block] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [idx_name] ON [#__users]
(
	[name] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

CREATE NONCLUSTERED INDEX [username] ON [#__users]
(
	[username] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Table [#__viewlevels] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__viewlevels](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL DEFAULT '',
	[ordering] [int] NOT NULL DEFAULT 0,
	[rules] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__viewlevels_id] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
 CONSTRAINT [#__viewlevels$idx_assetgroup_title_lookup] UNIQUE NONCLUSTERED
(
	[title] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

SET IDENTITY_INSERT [#__viewlevels] ON;

INSERT INTO [#__viewlevels] ([id], [title], [ordering], [rules])
SELECT 1, 'Public', 0, '[1]'
UNION ALL
SELECT 2, 'Registered', 2, '[6,2,8]'
UNION ALL
SELECT 3, 'Special', 3, '[6,3,8]'
UNION ALL
SELECT 5, 'Guest', 1, '[9]'
UNION ALL
SELECT 6, 'Super Users', 4, '[8]';

SET IDENTITY_INSERT [#__viewlevels] OFF;
