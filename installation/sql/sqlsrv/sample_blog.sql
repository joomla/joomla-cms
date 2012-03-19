SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__assets]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__assets](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[parent_id] [int] NOT NULL,
	[lft] [int] NOT NULL,
	[rgt] [int] NOT NULL,
	[level] [bigint] NOT NULL,
	[name] [nvarchar](50) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[rules] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__assets_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__assets]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_lft_rgt] ON [#__assets] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__assets]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_parent_id] ON [#__assets] 
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__a__paren__7E6CC920]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__a__paren__7E6CC920]') AND parent_object_id = OBJECT_ID(N'[#__assets]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__a__paren__7E6CC920]') AND type = 'D')
BEGIN
ALTER TABLE [#__assets] ADD  DEFAULT ((0)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__ass__lft__7F60ED59]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__lft__7F60ED59]') AND parent_object_id = OBJECT_ID(N'[#__assets]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__lft__7F60ED59]') AND type = 'D')
BEGIN
ALTER TABLE [#__assets] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__ass__rgt__00551192]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__assets]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__assets] ADD  DEFAULT ((0)) FOR [rgt]
END


End;


SET IDENTITY_INSERT [#__assets] ON;
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (1, 0, 1, 81, 0, N'root.1', N'Root Asset', N'{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (2, 1, 1, 2, 1, N'com_admin', N'com_admin', N'{}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (3, 1, 3, 6, 1, N'com_banners', N'com_banners', N'{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (4, 1, 7, 8, 1, N'com_cache', N'com_cache', N'{"core.admin":{"7":1},"core.manage":{"7":1}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (5, 1, 9, 10, 1, N'com_checkin', N'com_checkin', N'{"core.admin":{"7":1},"core.manage":{"7":1}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (6, 1, 11, 12, 1, N'com_config', N'com_config', N'{}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (7, 1, 13, 16, 1, N'com_contact', N'com_contact', N'{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (8, 1, 17, 32, 1, N'com_content', N'com_content', N'{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (9, 1, 33, 34, 1, N'com_cpanel', N'com_cpanel', N'{}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (10, 1, 35, 36, 1, N'com_installer', N'com_installer', N'{"core.admin":[],"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (11, 1, 37, 38, 1, N'com_languages', N'com_languages', N'{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (12, 1, 39, 40, 1, N'com_login', N'com_login', N'{}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (13, 1, 41, 42, 1, N'com_mailto', N'com_mailto', N'{}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (14, 1, 43, 44, 1, N'com_massmail', N'com_massmail', N'{}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (15, 1, 45, 46, 1, N'com_media', N'com_media', N'{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (16, 1, 47, 48, 1, N'com_menus', N'com_menus', N'{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (17, 1, 49, 50, 1, N'com_messages', N'com_messages', N'{"core.admin":{"7":1},"core.manage":{"7":1}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (18, 1, 51, 52, 1, N'com_modules', N'com_modules', N'{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (19, 1, 53, 56, 1, N'com_newsfeeds', N'com_newsfeeds', N'{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (20, 1, 57, 58, 1, N'com_plugins', N'com_plugins', N'{"core.admin":{"7":1},"core.manage":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (21, 1, 59, 60, 1, N'com_redirect', N'com_redirect', N'{"core.admin":{"7":1},"core.manage":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (22, 1, 61, 62, 1, N'com_search', N'com_search', N'{"core.admin":{"7":1},"core.manage":{"6":1}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (23, 1, 63, 64, 1, N'com_templates', N'com_templates', N'{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (24, 1, 65, 68, 1, N'com_users', N'com_users', N'{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.own":{"6":1},"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (25, 1, 69, 74, 1, N'com_weblinks', N'com_weblinks', N'{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (26, 1, 75, 76, 1, N'com_wrapper', N'com_wrapper', N'{}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (27, 8, 18, 23, 2, N'com_content.category.2', N'Uncategorised', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (28, 3, 4, 5, 2, N'com_banners.category.3', N'Uncategorised', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (29, 7, 14, 15, 2, N'com_contact.category.4', N'Uncategorised', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (30, 19, 54, 55, 2, N'com_newsfeeds.category.5', N'Uncategorised', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (31, 25, 70, 71, 2, N'com_weblinks.category.6', N'Uncategorised', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (32, 24, 66, 67, 1, N'com_users.notes.category.7', N'Uncategorised', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (33, 1, 77, 78, 1, N'com_finder', N'com_finder', N'{"core.admin":{"7":1},"core.manage":{"6":1}}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (34, 25, 72, 73, 2, N'com_weblinks.category.8', N'Blog Roll', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (35, 8, 24, 31, 2, N'com_content.category.9', N'Blog', N'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (36, 27, 19, 20, 3, N'com_content.article.1', N'About', N'{"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (37, 27, 21, 22, 3, N'com_content.article.2', N'Working on Your Site', N'{"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (38, 35, 25, 26, 3, N'com_content.article.3', N'Welcome to your blog', N'');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (39, 35, 27, 28, 3, N'com_content.article.4', N'About your home page', N'{"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (40, 35, 29, 30, 3, N'com_content.article.5', N'Your Modules', N'{"core.delete":[],"core.edit":[],"core.edit.state":[]}');
INSERT INTO[#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules]) VALUES (41, 1, 79, 80, 1, N'com_users.notes.category.10', N'Uncategorised', N'');
SET IDENTITY_INSERT [#__assets] OFF;


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__associations]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__associations](
	[id] [nvarchar](50) NOT NULL,
	[context] [nvarchar](50) NOT NULL,
	[key] [nchar](32) NOT NULL,
 CONSTRAINT [PK_#__associations_context] PRIMARY KEY CLUSTERED 
(
	[context] ASC,
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__associations]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_key] ON [#__associations] 
(
	[key] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__banners]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__banners](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[cid] [int] NOT NULL,
	[type] [int] NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[imptotal] [int] NOT NULL,
	[impmade] [int] NOT NULL,
	[clicks] [int] NOT NULL,
	[clickurl] [nvarchar](200) NOT NULL,
	[state] [smallint] NOT NULL,
	[catid] [bigint] NOT NULL,
	[description] [nvarchar](max) NOT NULL,
	[custombannercode] [nvarchar](2048) NOT NULL,
	[sticky] [tinyint] NOT NULL,
	[ordering] [int] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[own_prefix] [smallint] NOT NULL,
	[metakey_prefix] [nvarchar](255) NOT NULL,
	[purchase_type] [smallint] NOT NULL,
	[track_clicks] [smallint] NOT NULL,
	[track_impressions] [smallint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[publish_up] [datetime2](0) NOT NULL,
	[publish_down] [datetime2](0) NOT NULL,
	[reset] [datetime2](0) NOT NULL,
	[created] [datetime2](0) NOT NULL,
	[language] [nchar](7) NOT NULL,
 CONSTRAINT [PK_#__banners_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_banner_catid] ON [#__banners] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__banners] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_metakey_prefix] ON [#__banners] 
(
	[metakey_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_own_prefix] ON [#__banners] 
(
	[own_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__banners] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Default [DF__#__ban__cid__108B795B]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ban__cid__108B795B]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ban__cid__108B795B]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [cid]
END


End;

/****** Object:  Default [DF__#__ba__type__117F9D94]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ba__type__117F9D94]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ba__type__117F9D94]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [type]
END


End;

/****** Object:  Default [DF__#__ba__name__1273C1CD]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ba__name__1273C1CD]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ba__name__1273C1CD]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__b__alias__1367E606]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__alias__1367E606]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__alias__1367E606]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__b__impto__145C0A3F]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__impto__145C0A3F]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__impto__145C0A3F]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [imptotal]
END


End;

/****** Object:  Default [DF__#__b__impma__15502E78]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__impma__15502E78]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__impma__15502E78]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [impmade]
END


End;

/****** Object:  Default [DF__#__b__click__164452B1]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__click__164452B1]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__click__164452B1]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [clicks]
END


End;

/****** Object:  Default [DF__#__b__click__173876EA]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__click__173876EA]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__click__173876EA]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [clickurl]
END


End;

/****** Object:  Default [DF__#__b__state__182C9B23]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__state__182C9B23]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__state__182C9B23]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__b__catid__1920BF5C]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__catid__1920BF5C]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__catid__1920BF5C]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__b__stick__1A14E395]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__stick__1A14E395]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__stick__1A14E395]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [sticky]
END


End;

/****** Object:  Default [DF__#__b__order__1B0907CE]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__order__1B0907CE]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__order__1B0907CE]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__b__own_p__1BFD2C07]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__own_p__1BFD2C07]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__own_p__1BFD2C07]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [own_prefix]
END


End;

/****** Object:  Default [DF__#__b__metak__1CF15040]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__metak__1CF15040]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__metak__1CF15040]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [metakey_prefix]
END


End;

/****** Object:  Default [DF__#__b__purch__1DE57479]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__purch__1DE57479]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__purch__1DE57479]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((-1)) FOR [purchase_type]
END


End;

/****** Object:  Default [DF__#__b__track__1ED998B2]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__track__1ED998B2]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__track__1ED998B2]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((-1)) FOR [track_clicks]
END


End;

/****** Object:  Default [DF__#__b__track__1FCDBCEB]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__track__1FCDBCEB]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__track__1FCDBCEB]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((-1)) FOR [track_impressions]
END


End;

/****** Object:  Default [DF__#__b__check__20C1E124]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__check__20C1E124]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__check__20C1E124]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__b__check__21B6055D]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__check__21B6055D]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__check__21B6055D]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__b__publi__22AA2996]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__publi__22AA2996]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__publi__22AA2996]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (getdate()) FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__b__publi__239E4DCF]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__publi__239E4DCF]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__publi__239E4DCF]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (getdate()) FOR [publish_down]
END


End;

/****** Object:  Default [DF__#__b__reset__24927208]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__reset__24927208]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__reset__24927208]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (getdate()) FOR [reset]
END


End;

/****** Object:  Default [DF__#__b__creat__25869641]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__creat__25869641]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__creat__25869641]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (getdate()) FOR [created]
END


End;

/****** Object:  Default [DF__#__b__langu__267ABA7A]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__langu__267ABA7A]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__langu__267ABA7A]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Table [#__banner_clients]    Script Date: 03/16/2012 11:47:41 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__banners]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__banner_clients](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[contact] [nvarchar](255) NOT NULL,
	[email] [nvarchar](255) NOT NULL,
	[extrainfo] [nvarchar](max) NOT NULL,
	[state] [smallint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[own_prefix] [smallint] NOT NULL,
	[metakey_prefix] [nvarchar](255) NOT NULL,
	[purchase_type] [smallint] NOT NULL,
	[track_clicks] [smallint] NOT NULL,
	[track_impressions] [smallint] NOT NULL,
 CONSTRAINT [PK_#__banner_clients_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_clients]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_metakey_prefix] ON [#__banner_clients] 
(
	[metakey_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_clients]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_own_prefix] ON [#__banner_clients] 
(
	[own_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Default [DF__#__ba__name__03317E3D]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ba__name__03317E3D]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ba__name__03317E3D]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__b__conta__0425A276]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__conta__0425A276]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__conta__0425A276]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [contact]
END


End;

/****** Object:  Default [DF__#__b__email__0519C6AF]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__email__0519C6AF]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__email__0519C6AF]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [email]
END


End;

/****** Object:  Default [DF__#__b__state__060DEAE8]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__state__060DEAE8]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__state__060DEAE8]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__b__check__07020F21]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__check__07020F21]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__check__07020F21]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__b__check__07F6335A]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__check__07F6335A]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__check__07F6335A]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__b__own_p__08EA5793]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__own_p__08EA5793]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__own_p__08EA5793]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((0)) FOR [own_prefix]
END


End;

/****** Object:  Default [DF__#__b__metak__09DE7BCC]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__metak__09DE7BCC]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__metak__09DE7BCC]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [metakey_prefix]
END


End;

/****** Object:  Default [DF__#__b__purch__0AD2A005]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__purch__0AD2A005]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__purch__0AD2A005]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((-1)) FOR [purchase_type]
END


End;

/****** Object:  Default [DF__#__b__track__0BC6C43E]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__track__0BC6C43E]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__track__0BC6C43E]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((-1)) FOR [track_clicks]
END


End;

/****** Object:  Default [DF__#__b__track__0CBAE877]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__track__0CBAE877]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__track__0CBAE877]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((-1)) FOR [track_impressions]
END


End;

/****** Object:  Table [#__banner_tracks]    Script Date: 03/16/2012 11:47:41 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__banner_tracks](
	[track_date] [datetime2](0) NOT NULL,
	[track_type] [bigint] NOT NULL,
	[banner_id] [bigint] NOT NULL,
	[count] [bigint] NOT NULL,
 CONSTRAINT [PK_#__banner_tracks_track_date] PRIMARY KEY CLUSTERED 
(
	[track_date] ASC,
	[track_type] ASC,
	[banner_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_banner_id] ON [#__banner_tracks] 
(
	[banner_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_track_date] ON [#__banner_tracks] 
(
	[track_date] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_track_type] ON [#__banner_tracks] 
(
	[track_type] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__sharp$__b__count__0EA330E9]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__b__count__0EA330E9]') AND parent_object_id = OBJECT_ID(N'[#__banner_tracks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__b__count__0EA330E9]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_tracks] ADD  DEFAULT ((0)) FOR [count]
END


End;


/****** Object:  Table [#__categories]    Script Date: 03/16/2012 11:47:41 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__categories]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__categories](
	[id] [int] IDENTITY(9,1) NOT NULL,
	[asset_id] [bigint] NOT NULL,
	[parent_id] [bigint] NOT NULL,
	[lft] [int] NOT NULL,
	[rgt] [int] NOT NULL,
	[level] [bigint] NOT NULL,
	[path] [nvarchar](255) NOT NULL,
	[extension] [nvarchar](50) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[note] [nvarchar](255) NOT NULL,
	[description] [nvarchar](max) NOT NULL,
	[published] [smallint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[access] [bigint] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](1024) NOT NULL,
	[metakey] [nvarchar](1024) NOT NULL,
	[metadata] [nvarchar](2048) NOT NULL,
	[created_user_id] [bigint] NOT NULL,
	[created_time] [datetime2](0) NOT NULL,
	[modified_user_id] [bigint] NOT NULL,
	[modified_time] [datetime2](0) NOT NULL,
	[hits] [bigint] NOT NULL,
	[language] [nchar](7) NOT NULL,
 CONSTRAINT [PK_#__categories_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [cat_idx] ON [#__categories] 
(
	[extension] ASC,
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_access] ON [#__categories] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_alias] ON [#__categories] 
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__categories] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__categories] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__categories] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_path] ON [#__categories] 
(
	[path] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__c__asset__286302EC]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__asset__286302EC]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__asset__286302EC]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [asset_id]
END


End;

/****** Object:  Default [DF__#__c__paren__29572725]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__paren__29572725]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__paren__29572725]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__cat__lft__2A4B4B5E]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__cat__lft__2A4B4B5E]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__cat__lft__2A4B4B5E]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__cat__rgt__2B3F6F97]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__cat__rgt__2B3F6F97]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__cat__rgt__2B3F6F97]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [rgt]
END


End;

/****** Object:  Default [DF__#__c__level__2C3393D0]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__level__2C3393D0]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__level__2C3393D0]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [level]
END


End;

/****** Object:  Default [DF__#__ca__path__2D27B809]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ca__path__2D27B809]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ca__path__2D27B809]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [path]
END


End;

/****** Object:  Default [DF__#__c__exten__2E1BDC42]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__exten__2E1BDC42]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__exten__2E1BDC42]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [extension]
END


End;

/****** Object:  Default [DF__#__c__alias__2F10007B]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__alias__2F10007B]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__alias__2F10007B]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__ca__note__300424B4]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ca__note__300424B4]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ca__note__300424B4]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [note]
END


End;

/****** Object:  Default [DF__#__c__publi__30F848ED]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__publi__30F848ED]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__publi__30F848ED]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__c__check__31EC6D26]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__check__31EC6D26]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__check__31EC6D26]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__c__check__32E0915F]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__check__32E0915F]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__check__32E0915F]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__c__acces__33D4B598]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__acces__33D4B598]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__acces__33D4B598]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__c__creat__34C8D9D1]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [created_user_id]
END


End;
/****** Object:  Default [DF__#__c__creat__35BCFE0A]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (getdate()) FOR [created_time]
END


End;
/****** Object:  Default [DF__#__c__modif__36B12243]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [modified_user_id]
END


End;
/****** Object:  Default [DF__#__c__modif__37A5467C]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (getdate()) FOR [modified_time]
END


End;

/****** Object:  Default [DF__#__ca__hits__38996AB5]    Script Date: 03/16/2012 11:47:41 ******/

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [hits]
END


End;

SET IDENTITY_INSERT [#__categories] ON;
INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('1', '0', '0', '0', '19', '0', '', 'system', 'ROOT', 'root', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{}', '', '', '', '0', '2009-10-18 16:07:09', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('2', '27', '1', '1', '2', '1', 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:26:37', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('3', '28', '1', '3', '4', '1', 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"target":"","image":"","foobar":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:27:35', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('4', '29', '1', '5', '6', '1', 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:27:57', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('5', '30', '1', '7', '8', '1', 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:28:15', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('6', '31', '1', '9', '10', '1', 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:28:33', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('7', '32', '1', '11', '12', '1', 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:28:33', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('8', '34', '1', '13', '14', '1', 'blog-roll', 'com_weblinks', 'Blog Roll', 'blog-roll', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2012-01-04 15:02:08', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('9', '35', '1', '15', '16', '1', 'blog', 'com_content', 'Blog', 'blog', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2012-01-04 15:43:10', '0', '1900-01-01T00:00:00.000', '0', '*');

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language]) VALUES 
('10', '41', '1', '17', '18', '1', 'uncategorised', 'com_users.notes', 'Uncategorised', 'uncategorised', '', '', '1', '0', '1900-01-01T00:00:00.000', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2012-03-04 22:58:21', '0', '1900-01-01T00:00:00.000', '0', '*');
SET IDENTITY_INSERT [#__categories] OFF;


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__contact_details](
	[id] [int] IDENTITY(2,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
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
	[imagepos] [nvarchar](20) NULL,
	[email_to] [nvarchar](255) NULL,
	[default_con] [tinyint] NOT NULL,
	[published] [smallint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[ordering] [int] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[user_id] [int] NOT NULL,
	[catid] [int] NOT NULL,
	[access] [bigint] NOT NULL,
	[mobile] [nvarchar](255) NOT NULL,
	[webpage] [nvarchar](255) NOT NULL,
	[sortname1] [nvarchar](255) NOT NULL,
	[sortname2] [nvarchar](255) NOT NULL,
	[sortname3] [nvarchar](255) NOT NULL,
	[language] [nchar](7) NOT NULL,
	[created] [datetime2](0) NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime2](0) NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[featured] [tinyint] NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime2](0) NOT NULL,
	[publish_down] [datetime2](0) NOT NULL,
 CONSTRAINT [PK_#__contact_details_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_access] ON [#__contact_details] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__contact_details] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__contact_details] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__contact_details] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__contact_details] 
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__contact_details] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__contact_details] 
(
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__contact_details] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__co__name__3A81B327]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__co__name__3A81B327]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__co__name__3A81B327]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__c__alias__3B75D760]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__alias__3B75D760]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__alias__3B75D760]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__c__con_p__3C69FB99]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__con_p__3C69FB99]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__con_p__3C69FB99]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [con_position]
END


End;

/****** Object:  Default [DF__#__c__subur__3D5E1FD2]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__subur__3D5E1FD2]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__subur__3D5E1FD2]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [suburb]
END


End;

/****** Object:  Default [DF__#__c__state__3E52440B]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__state__3E52440B]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__state__3E52440B]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [state]
END


End;
/****** Object:  Default [DF__#__c__count__3F466844]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__count__3F466844]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__count__3F466844]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [country]
END


End;

/****** Object:  Default [DF__#__c__postc__403A8C7D]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__postc__403A8C7D]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__postc__403A8C7D]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [postcode]
END


End;

/****** Object:  Default [DF__#__c__telep__412EB0B6]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__telep__412EB0B6]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__telep__412EB0B6]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [telephone]
END


End;

/****** Object:  Default [DF__#__con__fax__4222D4EF]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__con__fax__4222D4EF]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__con__fax__4222D4EF]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [fax]
END


End;

/****** Object:  Default [DF__#__c__image__4316F928]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__image__4316F928]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__image__4316F928]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [image]
END


End;

/****** Object:  Default [DF__#__c__image__440B1D61]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__image__440B1D61]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__image__440B1D61]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [imagepos]
END


End;

/****** Object:  Default [DF__#__c__email__44FF419A]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__email__44FF419A]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__email__44FF419A]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [email_to]
END


End;

/****** Object:  Default [DF__#__c__defau__45F365D3]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__defau__45F365D3]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__defau__45F365D3]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [default_con]
END


End;

/****** Object:  Default [DF__#__c__publi__46E78A0C]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__publi__46E78A0C]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__publi__46E78A0C]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__c__check__47DBAE45]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__check__47DBAE45]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__check__47DBAE45]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__c__check__48CFD27E]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__check__48CFD27E]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__check__48CFD27E]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__c__order__49C3F6B7]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__order__49C3F6B7]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__order__49C3F6B7]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__c__user___4AB81AF0]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__user___4AB81AF0]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__user___4AB81AF0]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [user_id]
END


End;

/****** Object:  Default [DF__#__c__catid__4BAC3F29]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__catid__4BAC3F29]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__catid__4BAC3F29]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__c__acces__4CA06362]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__acces__4CA06362]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__acces__4CA06362]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__c__mobil__4D94879B]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__mobil__4D94879B]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__mobil__4D94879B]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [mobile]
END


End;

/****** Object:  Default [DF__#__c__webpa__4E88ABD4]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__webpa__4E88ABD4]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__webpa__4E88ABD4]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [webpage]
END


End;

/****** Object:  Default [DF__#__c__creat__4F7CD00D]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__creat__4F7CD00D]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__creat__4F7CD00D]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (getdate()) FOR [created]
END


End;

/****** Object:  Default [DF__#__c__creat__5070F446]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__creat__5070F446]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__creat__5070F446]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [created_by]
END


End;

/****** Object:  Default [DF__#__c__creat__5165187F]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__creat__5165187F]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__creat__5165187F]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [created_by_alias]
END


End;

/****** Object:  Default [DF__#__c__modif__52593CB8]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__modif__52593CB8]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__modif__52593CB8]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (getdate()) FOR [modified]
END


End;

/****** Object:  Default [DF__#__c__modif__534D60F1]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__modif__534D60F1]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__modif__534D60F1]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__c__featu__5441852A]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__featu__5441852A]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__featu__5441852A]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [featured]
END


End;

/****** Object:  Default [DF__#__c__publi__5535A963]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__publi__5535A963]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__publi__5535A963]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (getdate()) FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__c__publi__5629CD9C]    Script Date: 03/16/2012 12:02:12 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__publi__5629CD9C]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__publi__5629CD9C]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (getdate()) FOR [publish_down]
END


End;

/****** Object:  Table [#__content]    Script Date: 03/16/2012 11:47:41 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__content]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__content](
	[id] [bigint] IDENTITY(7,1) NOT NULL,
	[asset_id] [bigint] NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[title_alias] [nvarchar](255) NOT NULL,
	[introtext] [nvarchar](max) NOT NULL,
	[fulltext] [nvarchar](max) NOT NULL,
	[state] [smallint] NOT NULL,
	[sectionid] [bigint] NOT NULL,
	[mask] [bigint] NOT NULL,
	[catid] [bigint] NOT NULL,
	[created] [datetime2](0) NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime2](0) NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[publish_up] [datetime2](0) NOT NULL,
	[publish_down] [datetime2](0) NOT NULL,
	[images] [nvarchar](max) NOT NULL,
	[urls] [nvarchar](max) NOT NULL,
	[attribs] [nvarchar](max) NOT NULL,
	[version] [bigint] NOT NULL,
	[parentid] [bigint] NOT NULL,
	[ordering] [int] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[access] [bigint] NOT NULL,
	[hits] [bigint] NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[featured] [tinyint] NOT NULL,
	[language] [nchar](7) NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
 CONSTRAINT [PK_#__content_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_access] ON [#__content] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__content] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__content] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__content] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__content] 
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__content] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__content] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__content] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Default [DF__#__c__modif__6383C8BA]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__modif__6383C8BA]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__modif__6383C8BA]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [modified_by]

END


End;
/****** Object:  Default [DF__#__c__check__6477ECF3]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__check__6477ECF3]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__check__6477ECF3]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [checked_out]

END


End;
/****** Object:  Default [DF__#__c__check__656C112C]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__check__656C112C]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__check__656C112C]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (getdate()) FOR [checked_out_time]

END


End;
/****** Object:  Default [DF__#__c__publi__66603565]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__publi__66603565]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__publi__66603565]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (getdate()) FOR [publish_up]

END


End;
/****** Object:  Default [DF__#__c__publi__6754599E]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__publi__6754599E]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__publi__6754599E]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (getdate()) FOR [publish_down]

END


End;
/****** Object:  Default [DF__#__c__versi__68487DD7]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__versi__68487DD7]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__versi__68487DD7]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((1)) FOR [version]

END


End;
/****** Object:  Default [DF__#__c__paren__693CA210]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__paren__693CA210]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__paren__693CA210]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [parentid]

END


End;
/****** Object:  Default [DF__#__c__order__6A30C649]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__order__6A30C649]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__order__6A30C649]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [ordering]

END


End;
/****** Object:  Default [DF__#__c__acces__6B24EA82]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__acces__6B24EA82]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__acces__6B24EA82]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [access]

END


End;
/****** Object:  Default [DF__#__co__hits__6C190EBB]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__co__hits__6C190EBB]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__co__hits__6C190EBB]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [hits]

END


End;
/****** Object:  Default [DF__#__c__featu__6D0D32F4]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__featu__6D0D32F4]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__featu__6D0D32F4]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [featured]

END


End;

/****** Object:  Default [DF__#__c__asset__5812160E]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__asset__5812160E]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__asset__5812160E]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [asset_id]

END


End;
/****** Object:  Default [DF__#__c__title__59063A47]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__title__59063A47]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__title__59063A47]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [title]

END


End;
/****** Object:  Default [DF__#__c__alias__59FA5E80]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__alias__59FA5E80]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__alias__59FA5E80]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [alias]

END


End;
/****** Object:  Default [DF__#__c__title__5AEE82B9]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__title__5AEE82B9]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__title__5AEE82B9]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [title_alias]

END


End;
/****** Object:  Default [DF__#__c__state__5BE2A6F2]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__state__5BE2A6F2]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__state__5BE2A6F2]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [state]

END


End;
/****** Object:  Default [DF__#__c__secti__5CD6CB2B]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__secti__5CD6CB2B]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__secti__5CD6CB2B]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [sectionid]

END


End;
/****** Object:  Default [DF__#__co__mask__5DCAEF64]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__co__mask__5DCAEF64]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__co__mask__5DCAEF64]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [mask]

END


End;
/****** Object:  Default [DF__#__c__catid__5EBF139D]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__catid__5EBF139D]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__catid__5EBF139D]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [catid]

END


End;
/****** Object:  Default [DF__#__c__creat__5FB337D6]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__creat__5FB337D6]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__creat__5FB337D6]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (getdate()) FOR [created]

END


End;
/****** Object:  Default [DF__#__c__creat__60A75C0F]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__creat__60A75C0F]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__creat__60A75C0F]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [created_by]

END


End;
/****** Object:  Default [DF__#__c__creat__619B8048]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__creat__619B8048]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__creat__619B8048]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [created_by_alias]

END


End;
/****** Object:  Default [DF__#__c__modif__628FA481]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__c__modif__628FA481]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__c__modif__628FA481]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (getdate()) FOR [modified]

END


End;

SET IDENTITY_INSERT [#__content] ON;

INSERT INTO [#__content] ( [id], [asset_id], [title], [alias], [title_alias], [introtext], [fulltext], [state], [sectionid], [mask], [catid], [created], [created_by], [created_by_alias], [modified], [modified_by], [checked_out], [checked_out_time], [publish_up], [publish_down], [images], [urls], [attribs], [version], [parentid], [ordering], [metakey], [metadesc], [access], [hits], [metadata], [featured], [language], [xreference]) VALUES
(1, 36, 'About', 'about', '', '<p>This tells you a bit about this blog and the person who writes it. </p><p>When you are logged in you will be able to edit this page by clicking on the edit icon.</p>', '', 1, 0, 0, 2, '2012-01-04 16:10:42', 42, '', '1900-01-01T00:00:00.000', 0, 0, '1900-01-01T00:00:00.000', '2012-01-04 16:10:42', '1900-01-01T00:00:00.000', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}', '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 1, 0, 1, '', '', 1, 3, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', '');
INSERT INTO [#__content] ( [id], [asset_id], [title], [alias], [title_alias], [introtext], [fulltext], [state], [sectionid], [mask], [catid], [created], [created_by], [created_by_alias], [modified], [modified_by], [checked_out], [checked_out_time], [publish_up], [publish_down], [images], [urls], [attribs], [version], [parentid], [ordering], [metakey], [metadesc], [access], [hits], [metadata], [featured], [language], [xreference]) VALUES(2, 37, 'Working on Your Site', 'working-on-your-site', '', '<p>Here are some basic tips for working on your site.</p><ul><li>Joomla! has a "front end" that you are looking at now and an "administrator" which is where you do the more advanced work of creating your site such as setting up the menus and deciding what modules to show. You need to login to the administrator separately using the same user name and password that you used to login to this part of the site.</li><li>One of the first things you will probably want to do is change the site title and tag line. To do this login to the site administrator and on the Extensions menu click Template. This site installs with the Beez2 template with the "Beez2 - Default Style." Click on that and you will see a form were you can change these to what ever you want. You will also see other options that you can experiment with. </li><li>To totally change the look of you site you will probably want to install a new template. In the Extensions menu click on Extensions Manager and then go to the Install tab. There are many free and commercial templates available for Joomla.</li><li>As you have already seen, you can control who can see different parts of you site. When you work with modules, articles or weblinks setting the Access level to Registered will mean that only logged in users can see them</li><li>When you create a new article or other kind of content you also can save it as Published or Unpublished. If it is Unpublished site visitors will not be able to see it but you will.</li><li>You can learn much more about working with Joomla from the <a href="http://docs.joomla.org">Joomla documentation site</a> and get help from other users at the <a href="http://forum.joomla.org">Joomla forums</a>. In the administrator there are help buttons on every page that provide detailed information about the functions on that page.</li></ul>', '', 1, 0, 0, 2, '2012-01-04 16:48:38', 42, '', '2012-01-17 16:02:30', 42, 0, '1900-01-01T00:00:00.000', '2012-01-04 16:48:38', '1900-01-01T00:00:00.000', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}', '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 6, 0, 0, '', '', 3, 7, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', '');
INSERT INTO [#__content] ( [id], [asset_id], [title], [alias], [title_alias], [introtext], [fulltext], [state], [sectionid], [mask], [catid], [created], [created_by], [created_by_alias], [modified], [modified_by], [checked_out], [checked_out_time], [publish_up], [publish_down], [images], [urls], [attribs], [version], [parentid], [ordering], [metakey], [metadesc], [access], [hits], [metadata], [featured], [language], [xreference]) VALUES(3, 38, 'Welcome to your blog', 'welcome-to-your-blog', '', '<p>This is a sample blog posting. </p><p>If you log in to the site (the Author Login link is on the bottom of this page) you will be able to edit it and all of the other existing articles. You will also be able to create a new article.</p><p>As you add and modify articles you will see how your site changes and also how you can customise it in various ways.</p><p>Go ahead, you can''t break it. </p>', '', 1, 0, 0, 9, '2012-01-04 16:55:36', 42, '', '2012-01-17 16:03:05', 42, 0, '1900-01-01T00:00:00.000', '2012-01-04 16:55:36', '1900-01-01T00:00:00.000', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}', '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 3, 0, 2, '', '', 1, 1, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', '');
INSERT INTO [#__content] ( [id], [asset_id], [title], [alias], [title_alias], [introtext], [fulltext], [state], [sectionid], [mask], [catid], [created], [created_by], [created_by_alias], [modified], [modified_by], [checked_out], [checked_out_time], [publish_up], [publish_down], [images], [urls], [attribs], [version], [parentid], [ordering], [metakey], [metadesc], [access], [hits], [metadata], [featured], [language], [xreference]) VALUES(4, 39, 'About your home page', 'about-your-home-page', '', '<p>Your home page is set to display the four most recent articles from the blog category in a column. Then there are links to the 4 articles before those.  You can change those numbers by editing the content options settings in the blog tab in your site administrator.  There is a link to your site administrator in the top menu.</p><p>If you want to have your blog post broken into two parts, an introduction and then a full length separate page, use the Read More button to insert a break.</p>', '<p>On the full page you will see both the introductory content and the rest of the article. You can change the settings to hide the introduction if you want. </p><p> </p><p> </p><p> </p>', 1, 0, 0, 9, '2012-01-04 17:47:03', 42, '', '2012-01-04 18:16:23', 42, 0, '1900-01-01T00:00:00.000', '2012-01-04 17:47:03', '1900-01-01T00:00:00.000', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}', '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 2, 0, 1, '', '', 1, 1, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', '');
INSERT INTO [#__content] ( [id], [asset_id], [title], [alias], [title_alias], [introtext], [fulltext], [state], [sectionid], [mask], [catid], [created], [created_by], [created_by_alias], [modified], [modified_by], [checked_out], [checked_out_time], [publish_up], [publish_down], [images], [urls], [attribs], [version], [parentid], [ordering], [metakey], [metadesc], [access], [hits], [metadata], [featured], [language], [xreference]) VALUES(5, 40, 'Your Modules', 'your-modules', '', '<p>Your site has some commonly used modules already preconfigured. These include:</p><ul><li>Blog roll. which lets you link to other blogs. We''ve put in two examples, but you''ll want to change them. When you are logged in, click on edit blog roll to update this.</li><li>Most Read Posts which lists articles based on the number of times they have been read.</li><li>Older Articles which lists out articles by month. </li><li>Syndicate which allows your readers to read your posts in a news reader.</li></ul><p>Each of these modules has many options which you can experiment with in the Module Manager in your site Administrator. Joomla! also includes many other modules you can incorporate in your site. As you develop your site you may want to add more module that you can find at the <a href="http://extensions.joomla.org">Joomla Extensions Directory.</a></p>', '', 1, 0, 0, 9, '2012-01-05 09:30:17', 42, '', '2012-01-17 12:23:56', 42, 0, '1900-01-01T00:00:00.000', '2012-01-05 09:30:17', '1900-01-01T00:00:00.000', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}', '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 3, 0, 0, '', '', 1, 0, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', '');
SET IDENTITY_INSERT [#__content] OFF;

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__extensions]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__extensions](
	[extension_id] [int] IDENTITY(10000,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[type] [nvarchar](20) NOT NULL,
	[element] [nvarchar](100) NOT NULL,
	[folder] [nvarchar](100) NOT NULL,
	[client_id] [smallint] NOT NULL,
	[enabled] [smallint] NOT NULL,
	[access] [bigint] NOT NULL,
	[protected] [smallint] NOT NULL,
	[manifest_cache] [nvarchar](max) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[custom_data] [nvarchar](max) NOT NULL,
	[system_data] [nvarchar](max) NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[ordering] [int] NULL,
	[state] [int] NULL,
 CONSTRAINT [PK_#__extensions_extension_id] PRIMARY KEY CLUSTERED 
(
	[extension_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__extensions]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [element_clientid] ON [#__extensions] 
(
	[element] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__extensions]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [element_folder_clientid] ON [#__extensions] 
(
	[element] ASC,
	[folder] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__extensions]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [extension] ON [#__extensions] 
(
	[type] ASC,
	[element] ASC,
	[folder] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__e__enabl__797309D9]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__e__enabl__797309D9]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__e__enabl__797309D9]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((1)) FOR [enabled]
END


End;

/****** Object:  Default [DF__#__e__acces__7A672E12]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__e__acces__7A672E12]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__e__acces__7A672E12]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((1)) FOR [access]
END


End;

/****** Object:  Default [DF__#__e__prote__7B5B524B]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__e__prote__7B5B524B]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__e__prote__7B5B524B]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [protected]
END


End;

/****** Object:  Default [DF__#__e__check__7C4F7684]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__e__check__7C4F7684]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__e__check__7C4F7684]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__e__check__7D439ABD]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__e__check__7D439ABD]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__e__check__7D439ABD]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__e__order__7E37BEF6]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__e__order__7E37BEF6]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__e__order__7E37BEF6]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__e__state__7F2BE32F]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__e__state__7F2BE32F]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__e__state__7F2BE32F]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [state]
END


End;



SET IDENTITY_INSERT [#__extensions] ON;
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES
(1, 'com_mailto', 'component', 'com_mailto', '', 0, 1, 1, 1, '{"legacy":false,"name":"com_mailto","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MAILTO_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(2, 'com_wrapper', 'component', 'com_wrapper', '', 0, 1, 1, 1, '{"legacy":false,"name":"com_wrapper","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_WRAPPER_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(3, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_admin","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_ADMIN_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(4, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, '{"legacy":false,"name":"com_banners","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_BANNERS_XML_DESCRIPTION","group":""}', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":""}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(5, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_cache","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CACHE_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(6, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_categories","type":"component","creationDate":"December 2007","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CATEGORIES_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(7, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_checkin","type":"component","creationDate":"Unknown","author":"Joomla! Project","copyright":"(C) 2005 - 2008 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CHECKIN_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(8, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, '{"legacy":false,"name":"com_contact","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONTACT_XML_DESCRIPTION","group":""}', '{"show_contact_category":"hide","show_contact_list":"0","presentation_style":"sliders","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","image":"","allow_vcard":"0","show_articles":"0","show_profile":"0","show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","show_headings":"1","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","allow_vcard_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_category_crumb":"0","metakey":"","metadesc":"","robots":"","author":"","rights":"","xreference":""}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(9, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_cpanel","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CPANEL_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(10, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_installer","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_INSTALLER_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(11, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_languages","type":"component","creationDate":"2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_LANGUAGES_XML_DESCRIPTION","group":""}', '{"administrator":"en-GB","site":"en-GB"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(12, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_login","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_LOGIN_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(13, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, '{"legacy":false,"name":"com_media","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MEDIA_XML_DESCRIPTION","group":""}', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip","upload_mime_illegal":"text/html","enable_flash":"0"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(14, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_menus","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MENUS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(15, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_messages","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MESSAGES_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(16, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_modules","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MODULES_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(17, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, '{"legacy":false,"name":"com_newsfeeds","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_NEWSFEEDS_XML_DESCRIPTION","group":""}', '{"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_word_count":"0","show_headings":"1","show_name":"1","show_articles":"0","show_link":"1","show_description":"1","show_description_image":"1","display_num":"","show_pagination_limit":"1","show_pagination":"1","show_pagination_results":"1","show_cat_items":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(18, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_plugins","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_PLUGINS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(19, 'com_search', 'component', 'com_search', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_search","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_SEARCH_XML_DESCRIPTION","group":""}', '{"enabled":"0","show_date":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(20, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, '{"legacy":false,"name":"com_templates","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_TEMPLATES_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(21, 'com_weblinks', 'component', 'com_weblinks', '', 1, 1, 1, 0, '{"legacy":false,"name":"com_weblinks","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.nt","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_WEBLINKS_XML_DESCRIPTION","group":""}', '{"show_comp_description":"1","comp_description":"","show_link_hits":"1","show_link_description":"1","show_other_cats":"0","show_headings":"0","show_numbers":"0","show_report":"1","count_clicks":"1","target":"0","link_icons":""}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(22, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, '{"legacy":false,"name":"com_content","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONTENT_XML_DESCRIPTION","group":""}', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","show_category":"0","link_category":"0","show_parent_category":"0","link_parent_category":"0","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"0","show_noauth":"0","show_publishing_options":"1","show_article_options":"1","show_urls_images_frontend":"1","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"4","num_intro_articles":"0","num_columns":"1","num_links":"4","multi_column_order":"0","show_subcategory_content":"-1","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(23, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, '{"legacy":false,"name":"com_config","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONFIG_XML_DESCRIPTION","group":""}', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"NONE","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(24, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 1, '{"legacy":false,"name":"com_redirect","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_REDIRECT_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(25, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, '{"legacy":false,"name":"com_users","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_USERS_XML_DESCRIPTION","group":""}', '{"allowUserRegistration":"1","new_usertype":"2","useractivation":"1","frontend_userparams":"1","mailSubjectPrefix":"","mailBodySuffix":""}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(27, 'com_finder', 'component', 'com_finder', '', 1, 1, 0, 0, '{"legacy":false,"name":"com_finder","type":"component","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"COM_FINDER_XML_DESCRIPTION","group":""}', '{"show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_advanced":"1","expand_advanced":"0","show_date_filters":"0","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","memory_table_limit":30000,"title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stemmer":"snowball"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(100, 'PHPMailer', 'library', 'phpmailer', '', 0, 1, 1, 1, '{"legacy":false,"name":"PHPMailer","type":"library","creationDate":"2008","author":"PHPMailer","copyright":"Copyright (C) PHPMailer.","authorEmail":"","authorUrl":"http://phpmailer.codeworxtech.com/","version":"1.7.0","description":"LIB_PHPMAILER_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(101, 'SimplePie', 'library', 'simplepie', '', 0, 1, 1, 1, '{"legacy":false,"name":"SimplePie","type":"library","creationDate":"2008","author":"SimplePie","copyright":"Copyright (C) 2008 SimplePie","authorEmail":"","authorUrl":"http://simplepie.org/","version":"1.0.1","description":"LIB_SIMPLEPIE_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '{"legacy":false,"name":"phputf8","type":"library","creationDate":"2008","author":"Harry Fuecks","copyright":"Copyright various authors","authorEmail":"","authorUrl":"http://sourceforge.net/projects/phputf8","version":"1.7.0","description":"LIB_PHPUTF8_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(103, 'Joomla! Web Application Framework', 'library', 'joomla', '', 0, 1, 1, 1, '{"legacy":false,"name":"Joomla! Web Application Framework","type":"library","creationDate":"2008","author":"Joomla","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"http://www.joomla.org","version":"1.7.0","description":"LIB_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(200, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_articles_archive","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters.nttAll rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_ARCHIVE_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(201, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_articles_latest","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LATEST_NEWS_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(202, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_articles_popular","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_POPULAR_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(203, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_banners","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_BANNERS_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(204, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_breadcrumbs","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_BREADCRUMBS_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(205, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_custom","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_CUSTOM_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(206, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_feed","type":"module","creationDate":"July 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_FEED_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(207, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_footer","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_FOOTER_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(208, 'mod_login', 'module', 'mod_login', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_login","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LOGIN_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(209, 'mod_menu', 'module', 'mod_menu', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_menu","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_MENU_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(210, 'mod_articles_news', 'module', 'mod_articles_news', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_articles_news","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_NEWS_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(211, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_random_image","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_RANDOM_IMAGE_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(212, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_related_items","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_RELATED_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(213, 'mod_search', 'module', 'mod_search', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_search","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_SEARCH_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(214, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_stats","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_STATS_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(215, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_syndicate","type":"module","creationDate":"May 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_SYNDICATE_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(216, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_users_latest","type":"module","creationDate":"December 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_USERS_LATEST_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(217, 'mod_weblinks', 'module', 'mod_weblinks', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_weblinks","type":"module","creationDate":"July 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_WEBLINKS_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(218, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_whosonline","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_WHOSONLINE_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(219, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, '{"legacy":false,"name":"mod_wrapper","type":"module","creationDate":"October 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_WRAPPER_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(220, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_articles_category","type":"module","creationDate":"February 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_CATEGORY_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(221, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_articles_categories","type":"module","creationDate":"February 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_CATEGORIES_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(222, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '{"legacy":false,"name":"mod_languages","type":"module","creationDate":"February 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LANGUAGES_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(223, 'mod_finder', 'module', 'mod_finder', '', 0, 1, 0, 0, '{"legacy":false,"name":"mod_finder","type":"module","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"MOD_FINDER_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(300, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 1, 1, '{"legacy":false,"name":"mod_custom","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_CUSTOM_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(301, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_feed","type":"module","creationDate":"July 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_FEED_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(302, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_latest","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LATEST_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(303, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_logged","type":"module","creationDate":"January 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LOGGED_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(304, 'mod_login', 'module', 'mod_login', '', 1, 1, 1, 1, '{"legacy":false,"name":"mod_login","type":"module","creationDate":"March 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LOGIN_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(305, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_menu","type":"module","creationDate":"March 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_MENU_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(307, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_popular","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_POPULAR_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(308, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 1, 1, '{"legacy":false,"name":"mod_quickicon","type":"module","creationDate":"Nov 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_QUICKICON_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(309, 'mod_status', 'module', 'mod_status', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_status","type":"module","creationDate":"Feb 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_STATUS_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(310, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_submenu","type":"module","creationDate":"Feb 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_SUBMENU_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(311, 'mod_title', 'module', 'mod_title', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_title","type":"module","creationDate":"Nov 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_TITLE_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(312, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 1, 1, '{"legacy":false,"name":"mod_toolbar","type":"module","creationDate":"Nov 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_TOOLBAR_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(313, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_multilangstatus","type":"module","creationDate":"September 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"MOD_MULTILANGSTATUS_XML_DESCRIPTION","group":""}', '{"cache":"0"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(400, 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '{"legacy":false,"name":"plg_authentication_gmail","type":"plugin","creationDate":"February 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_GMAIL_XML_DESCRIPTION","group":""}', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(401, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, '{"legacy":false,"name":"plg_authentication_joomla","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_AUTH_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(402, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '{"legacy":false,"name":"plg_authentication_ldap","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_LDAP_XML_DESCRIPTION","group":""}', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', 0, '1900-01-01T00:00:00.000', 3, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(404, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '{"legacy":false,"name":"plg_content_emailcloak","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_EMAILCLOAK_XML_DESCRIPTION","group":""}', '{"mode":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(405, 'plg_content_geshi', 'plugin', 'geshi', 'content', 0, 0, 1, 0, '{"legacy":false,"name":"plg_content_geshi","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"","authorUrl":"qbnz.com/highlighter","version":"1.7.0","description":"PLG_CONTENT_GESHI_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 2, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(406, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '{"legacy":false,"name":"plg_content_loadmodule","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_LOADMODULE_XML_DESCRIPTION","group":""}', '{"style":"xhtml"}', '', '', 0, '2011-09-18 15:22:50', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(407, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 1, '{"legacy":false,"name":"plg_content_pagebreak","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_PAGEBREAK_XML_DESCRIPTION","group":""}', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 4, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(408, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 1, '{"legacy":false,"name":"plg_content_pagenavigation","type":"plugin","creationDate":"January 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_PAGENAVIGATION_XML_DESCRIPTION","group":""}', '{"position":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 5, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(409, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 1, 1, 1, '{"legacy":false,"name":"plg_content_vote","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_VOTE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 6, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(410, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '{"legacy":false,"name":"plg_editors_codemirror","type":"plugin","creationDate":"28 March 2011","author":"Marijn Haverbeke","copyright":"","authorEmail":"N/A","authorUrl":"","version":"1.0","description":"PLG_CODEMIRROR_XML_DESCRIPTION","group":""}', '{"linenumbers":"0","tabmode":"indent"}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(411, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, '{"legacy":false,"name":"plg_editors_none","type":"plugin","creationDate":"August 2004","author":"Unknown","copyright":"","authorEmail":"N/A","authorUrl":"","version":"1.7.0","description":"PLG_NONE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 2, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(412, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 1, '{"legacy":false,"name":"plg_editors_tinymce","type":"plugin","creationDate":"2005-2011","author":"Moxiecode Systems AB","copyright":"Moxiecode Systems AB","authorEmail":"N\\/A","authorUrl":"tinymce.moxiecode.com\\/","version":"3.4.7","description":"PLG_TINY_XML_DESCRIPTION","group":""}', '{"mode":"1","skin":"0","compressed":"0","cleanup_startup":"0","cleanup_save":"2","entity_encoding":"raw","lang_mode":"0","lang_code":"en","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","invalid_elements":"script,applet,iframe","extended_elements":"","toolbar":"top","toolbar_align":"left","html_height":"550","html_width":"750","element_path":"1","fonts":"1","paste":"1","searchreplace":"1","insertdate":"1","format_date":"%Y-%m-%d","inserttime":"1","format_time":"%H:%M:%S","colors":"1","table":"1","smilies":"1","media":"1","hr":"1","directionality":"1","fullscreen":"1","style":"1","layer":"1","xhtmlxtras":"1","visualchars":"1","nonbreaking":"1","template":"1","blockquote":"1","wordcount":"1","advimage":"1","advlink":"1","autosave":"1","contextmenu":"1","inlinepopups":"1","safari":"0","custom_plugin":"","custom_button":""}', '', '', 0, '1900-01-01T00:00:00.000', 3, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(413, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 1, '{"legacy":false,"name":"plg_editors-xtd_article","type":"plugin","creationDate":"October 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_ARTICLE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(414, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '{"legacy":false,"name":"plg_editors-xtd_image","type":"plugin","creationDate":"August 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_IMAGE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 2, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(415, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '{"legacy":false,"name":"plg_editors-xtd_pagebreak","type":"plugin","creationDate":"August 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_EDITORSXTD_PAGEBREAK_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 3, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(416, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '{"legacy":false,"name":"plg_editors-xtd_readmore","type":"plugin","creationDate":"March 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_READMORE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 4, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(417, 'plg_search_categories', 'plugin', 'categories', 'search', 0, 1, 1, 0, '{"legacy":false,"name":"plg_search_categories","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_CATEGORIES_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(418, 'plg_search_contacts', 'plugin', 'contacts', 'search', 0, 1, 1, 0, '{"legacy":false,"name":"plg_search_contacts","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_CONTACTS_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(419, 'plg_search_content', 'plugin', 'content', 'search', 0, 1, 1, 0, '{"legacy":false,"name":"plg_search_content","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_CONTENT_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(420, 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '{"legacy":false,"name":"plg_search_newsfeeds","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_NEWSFEEDS_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(421, 'plg_search_weblinks', 'plugin', 'weblinks', 'search', 0, 1, 1, 0, '{"legacy":false,"name":"plg_search_weblinks","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_WEBLINKS_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(422, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '{"legacy":false,"name":"plg_system_languagefilter","type":"plugin","creationDate":"July 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SYSTEM_LANGUAGEFILTER_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(423, 'plg_system_p3p', 'plugin', 'p3p', 'system', 0, 1, 1, 1, '{"legacy":false,"name":"plg_system_p3p","type":"plugin","creationDate":"September 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_P3P_XML_DESCRIPTION","group":""}', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', 0, '1900-01-01T00:00:00.000', 2, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(424, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 1, '{"legacy":false,"name":"plg_system_cache","type":"plugin","creationDate":"February 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CACHE_XML_DESCRIPTION","group":""}', '{"browsercache":"0","cachetime":"15"}', '', '', 0, '1900-01-01T00:00:00.000', 9, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(425, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '{"legacy":false,"name":"plg_system_debug","type":"plugin","creationDate":"December 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_DEBUG_XML_DESCRIPTION","group":""}', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', 0, '1900-01-01T00:00:00.000', 4, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(426, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 1, '{"legacy":false,"name":"plg_system_log","type":"plugin","creationDate":"April 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_LOG_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 5, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(427, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 1, 1, 1, '{"legacy":false,"name":"plg_system_redirect","type":"plugin","creationDate":"April 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_REDIRECT_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 6, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(428, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 1, '{"legacy":false,"name":"plg_system_remember","type":"plugin","creationDate":"April 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_REMEMBER_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 7, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(429, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, '{"legacy":false,"name":"plg_system_sef","type":"plugin","creationDate":"December 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEF_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 8, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(430, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 1, '{"legacy":false,"name":"plg_system_logout","type":"plugin","creationDate":"April 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SYSTEM_LOGOUT_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 3, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(431, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 1, '{"legacy":false,"name":"plg_user_contactcreator","type":"plugin","creationDate":"August 2009","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTACTCREATOR_XML_DESCRIPTION","group":""}', '{"autowebpage":"","category":"34","autopublish":"0"}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(432, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '{"legacy":false,"name":"plg_user_joomla","type":"plugin","creationDate":"December 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2009 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_USER_JOOMLA_XML_DESCRIPTION","group":""}', '{"autoregister":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 2, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(433, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 1, '{"legacy":false,"name":"plg_user_profile","type":"plugin","creationDate":"January 2008","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_USER_PROFILE_XML_DESCRIPTION","group":""}', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(434, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '{"legacy":false,"name":"plg_extension_joomla","type":"plugin","creationDate":"May 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_EXTENSION_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '{"legacy":false,"name":"plg_content_joomla","type":"plugin","creationDate":"November 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(436, 'plg_system_languagecode', 'plugin', 'languagecode', 'system', 0, 0, 1, 0, '{"legacy":false,"name":"plg_system_languagecode","type":"plugin","creationDate":"November 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SYSTEM_LANGUAGECODE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 10, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(437, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '{"legacy":false,"name":"plg_quickicon_joomlaupdate","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"PLG_QUICKICON_JOOMLAUPDATE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(438, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '{"legacy":false,"name":"plg_quickicon_extensionupdate","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"PLG_QUICKICON_EXTENSIONUPDATE_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(439, 'plg_captcha_recaptcha', 'plugin', 'recaptcha', 'captcha', 0, 1, 1, 0, '{"legacy":false,"name":"plg_captcha_recaptcha","type":"plugin","creationDate":"December 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_CAPTCHA_RECAPTCHA_XML_DESCRIPTION","group":""}', '{"public_key":"","private_key":"","theme":"clean"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(440, 'plg_system_highlight', 'plugin', 'highlight', 'system', 0, 1, 1, 0, '{"legacy":false,"name":"plg_system_highlight","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_SYSTEM_HIGHLIGHT_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 7, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(441, 'plg_content_finder', 'plugin', 'finder', 'content', 0, 0, 1, 0, '{"legacy":false,"name":"plg_content_finder","type":"plugin","creationDate":"December 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_FINDER_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(442, 'plg_finder_categories', 'plugin', 'categories', 'finder', 0, 1, 1, 0, '{"legacy":false,"name":"plg_finder_categories","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_FINDER_CATEGORIES_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 1, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(443, 'plg_finder_contacts', 'plugin', 'contacts', 'finder', 0, 1, 1, 0, '{"legacy":false,"name":"plg_finder_contacts","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_FINDER_CONTACTS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 2, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(444, 'plg_finder_content', 'plugin', 'content', 'finder', 0, 1, 1, 0, '{"legacy":false,"name":"plg_finder_content","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_FINDER_CONTENT_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 3, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(445, 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', 0, 1, 1, 0, '{"legacy":false,"name":"plg_finder_newsfeeds","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_FINDER_NEWSFEEDS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 4, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(446, 'plg_finder_weblinks', 'plugin', 'weblinks', 'finder', 0, 1, 1, 0, '{"legacy":false,"name":"plg_finder_weblinks","type":"plugin","creationDate":"August 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_FINDER_WEBLINKS_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 5, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(447, 'plg_system_finder', 'plugin', 'finder', 'system', 0, 0, 1, 0, '{"legacy":false,"name":"plg_system_finder","type":"plugin","creationDate":"September 2011","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"PLG_SYSTEM_FINDER_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 11, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(500, 'atomic', 'template', 'atomic', '', 0, 1, 1, 0, '{"legacy":false,"name":"atomic","type":"template","creationDate":"10/10/09","author":"Ron Severdia","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"contact@kontentdesign.com","authorUrl":"http://www.kontentdesign.com","version":"1.7.0","description":"TPL_ATOMIC_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(502, 'bluestork', 'template', 'bluestork', '', 1, 1, 1, 0, '{"legacy":false,"name":"bluestork","type":"template","creationDate":"07/02/09","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"TPL_BLUESTORK_XML_DESCRIPTION","group":""}', '{"useRoundedCorners":"1","showSiteName":"0","textBig":"0","highContrast":"0"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(503, 'beez_20', 'template', 'beez_20', '', 0, 1, 1, 0, '{"legacy":false,"name":"beez_20","type":"template","creationDate":"25 November 2009","author":"Angie Radtke","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"a.radtke@derauftritt.de","authorUrl":"http://www.der-auftritt.de","version":"1.7.0","description":"TPL_BEEZ2_XML_DESCRIPTION","group":""}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, '{"legacy":false,"name":"hathor","type":"template","creationDate":"May 2010","author":"Andrea Tarr","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"hathor@tarrconsulting.com","authorUrl":"http://www.tarrconsulting.com","version":"1.7.0","description":"TPL_HATHOR_XML_DESCRIPTION","group":""}', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(505, 'beez5', 'template', 'beez5', '', 0, 1, 1, 0, '{"legacy":false,"name":"beez5","type":"template","creationDate":"21 May 2010","author":"Angie Radtke","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"a.radtke@derauftritt.de","authorUrl":"http://www.der-auftritt.de","version":"1.7.0","description":"TPL_BEEZ5_XML_DESCRIPTION","group":""}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","html5":"0"}', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(600, 'English (United Kingdom)', 'language', 'en-GB', '', 0, 1, 1, 1, '{"legacy":false,"name":"English (United Kingdom)","type":"language","creationDate":"2008-03-15","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"en-GB site language","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(601, 'English (United Kingdom)', 'language', 'en-GB', '', 1, 1, 1, 1, '{"legacy":false,"name":"English (United Kingdom)","type":"language","creationDate":"2008-03-15","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"en-GB administrator language","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(700, 'files_joomla', 'file', 'joomla', '', 0, 1, 1, 1, '{"legacy":false,"name":"files_joomla","type":"file","creationDate":"December 2011","author":"Joomla!","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.0","description":"FILES_JOOMLA_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
INSERT INTO #__extensions ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state]) VALUES(800, 'PKG_JOOMLA', 'package', 'pkg_joomla', '', 0, 1, 1, 1, '{"legacy":false,"name":"PKG_JOOMLA","type":"package","creationDate":"2006","author":"Joomla!","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"http://www.joomla.org","version":"1.7.0","description":"PKG_JOOMLA_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01T00:00:00.000', 0, 0);
SET IDENTITY_INSERT [#__extensions] OFF;


/****** Object:  Table [#__finder_types]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_types]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_types](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[mime] [nvarchar](100) NOT NULL,
 CONSTRAINT [PK_#__finder_types_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Table [#__finder_tokens_aggregate]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_tokens_aggregate](
	[term_id] [bigint] NOT NULL,
	[map_suffix] [nchar](1) NOT NULL,
	[term] [nvarchar](75) NOT NULL,
	[stem] [nvarchar](75) NOT NULL,
	[common] [tinyint] NOT NULL,
	[phrase] [tinyint] NOT NULL,
	[term_weight] [real] NOT NULL,
	[context] [tinyint] NOT NULL,
	[context_weight] [real] NOT NULL,
	[total_weight] [real] NOT NULL
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [keyword_id] ON [#__finder_tokens_aggregate] 
(
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [token] ON [#__finder_tokens_aggregate] 
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__f__commo__3587F3E0]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__commo__3587F3E0]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__commo__3587F3E0]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens_aggregate] ADD  DEFAULT ((0)) FOR [common]
END


End;
/****** Object:  Default [DF__#__f__phras__367C1819]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__phras__367C1819]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__phras__367C1819]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens_aggregate] ADD  DEFAULT ((0)) FOR [phrase]
END


End;
/****** Object:  Default [DF__#__f__conte__37703C52]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__conte__37703C52]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__conte__37703C52]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens_aggregate] ADD  DEFAULT ((2)) FOR [context]
END


End;

/****** Object:  Table [#__finder_tokens]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_tokens]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_tokens](
	[term] [nvarchar](75) NOT NULL,
	[stem] [nvarchar](75) NOT NULL,
	[common] [tinyint] NOT NULL,
	[phrase] [tinyint] NOT NULL,
	[weight] [real] NOT NULL,
	[context] [tinyint] NOT NULL
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_context] ON [#__finder_tokens] 
(
	[context] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_word] ON [#__finder_tokens] 
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__f__commo__30C33EC3]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__commo__30C33EC3]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__commo__30C33EC3]') AND type = 'D')
BEGIN

ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((0)) FOR [common]
END


End;

/****** Object:  Default [DF__#__f__phras__31B762FC]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__phras__31B762FC]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__phras__31B762FC]') AND type = 'D')
BEGIN

ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((0)) FOR [phrase]
END


End;

/****** Object:  Default [DF__#__f__weigh__32AB8735]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__weigh__32AB8735]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__weigh__32AB8735]') AND type = 'D')
BEGIN

ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((1)) FOR [weight]
END


End;

/****** Object:  Default [DF__#__f__conte__339FAB6E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__conte__339FAB6E]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__conte__339FAB6E]') AND type = 'D')
BEGIN

ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((2)) FOR [context]
END


End;

/****** Object:  Table [#__finder_terms_common]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_terms_common]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_terms_common](
	[term] [nvarchar](75) NOT NULL,
	[language] [nvarchar](3) NOT NULL
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms_common]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_lang] ON [#__finder_terms_common] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms_common]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_word_lang] ON [#__finder_terms_common] 
(
	[term] ASC,
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'a', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'a', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'about', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'about', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'after', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'after', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'ago', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'ago', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'all', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'all', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'am', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'am', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'an', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'an', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'and', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'and', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'ani', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'ani', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'any', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'any', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'are', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'are', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'aren''t', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'aren''t', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'as', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'as', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'at', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'at', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'be', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'be', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'but', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'but', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'by', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'by', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'for', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'for', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'from', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'from', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'get', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'get', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'go', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'go', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'how', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'how', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'if', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'if', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'in', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'in', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'into', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'into', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'is', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'is', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'isn''t', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'isn''t', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'it', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'it', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'its', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'its', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'me', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'me', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'more', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'more', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'most', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'most', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'must', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'must', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'my', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'my', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'new', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'new', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'no', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'no', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'none', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'none', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'not', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'not', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'noth', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'noth', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'nothing', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'nothing', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'of', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'of', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'off', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'off', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'often', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'often', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'old', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'old', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'on', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'on', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'onc', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'onc', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'once', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'once', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'onli', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'onli', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'only', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'only', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'or', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'or', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'other', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'other', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'our', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'our', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'ours', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'ours', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'out', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'out', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'over', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'over', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'page', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'page', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'she', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'she', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'should', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'should', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'small', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'small', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'so', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'so', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'some', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'some', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'than', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'than', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'thank', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'thank', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'that', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'that', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'the', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'the', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'their', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'their', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'theirs', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'theirs', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'them', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'them', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'then', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'then', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'there', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'there', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'these', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'these', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'they', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'they', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'this', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'this', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'those', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'those', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'thus', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'thus', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'time', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'time', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'times', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'times', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'to', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'to', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'too', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'too', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'true', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'true', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'under', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'under', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'until', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'until', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'up', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'up', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'upon', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'upon', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'use', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'use', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'user', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'user', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'users', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'users', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'veri', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'veri', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'version', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'version', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'very', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'very', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'via', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'via', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'want', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'want', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'was', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'was', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'way', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'way', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'were', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'were', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'what', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'what', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'when', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'when', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'where', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'where', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'whi', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'whi', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'which', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'which', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'who', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'who', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'whom', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'whom', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'whose', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'whose', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'why', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'why', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'wide', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'wide', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'will', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'will', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'with', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'with', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'within', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'within', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'without', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'without', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'would', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'would', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'yes', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'yes', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'yet', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'yet', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'you', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'you', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'your', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'your', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'yours', N'en');
INSERT [dbo].[#__finder_terms_common] ([term], [language]) VALUES (N'yours', N'en');


/****** Object:  Table [#__finder_terms]    Script Date: 03/16/2012 11:47:40 ******/

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_terms]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_terms](
	[term_id] [bigint] IDENTITY(1,1) NOT NULL,
	[term] [nvarchar](75) NOT NULL,
	[stem] [nvarchar](75) NOT NULL,
	[common] [tinyint] NOT NULL,
	[phrase] [tinyint] NOT NULL,
	[weight] [real] NOT NULL,
	[soundex] [nvarchar](75) NOT NULL,
	[links] [int] NOT NULL,
 CONSTRAINT [PK_#__finder_terms_term_id] PRIMARY KEY CLUSTERED 
(
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_soundex_phrase] ON [#__finder_terms] 
(
	[soundex] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_stem_phrase] ON [#__finder_terms] 
(
	[stem] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_phrase] ON [#__finder_terms] 
(
	[term] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__f__commo__2B0A656D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__commo__2B0A656D]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__commo__2B0A656D]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [common]
END


End;

/****** Object:  Default [DF__#__f__phras__2BFE89A6]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__phras__2BFE89A6]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__phras__2BFE89A6]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [phrase]
END


End;

/****** Object:  Default [DF__#__f__weigh__2CF2ADDF]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__weigh__2CF2ADDF]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__weigh__2CF2ADDF]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [weight]
END


End;

/****** Object:  Default [DF__#__f__links__2DE6D218]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__links__2DE6D218]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__links__2DE6D218]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [links]
END


End;

/****** Object:  Table [#__finder_taxonomy_map]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy_map]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__finder_taxonomy_map](
	[link_id] [bigint] NOT NULL,
	[node_id] [bigint] NOT NULL,
 CONSTRAINT [PK_#__finder_taxonomy_map_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[node_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy_map]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [link_id] ON [#__finder_taxonomy_map] 
(
	[link_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy_map]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [node_id] ON [#__finder_taxonomy_map] 
(
	[node_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_taxonomy]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_taxonomy](
	[id] [bigint] IDENTITY(2,1) NOT NULL,
	[parent_id] [bigint] NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[state] [tinyint] NOT NULL,
	[access] [tinyint] NOT NULL,
	[ordering] [tinyint] NOT NULL,
 CONSTRAINT [PK_#__finder_taxonomy_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [access] ON [#__finder_taxonomy] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_parent_published] ON [#__finder_taxonomy] 
(
	[parent_id] ASC,
	[state] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [ordering] ON [#__finder_taxonomy] 
(
	[ordering] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [parent_id] ON [#__finder_taxonomy] 
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [state] ON [#__finder_taxonomy] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__f__paren__25518C17]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__paren__25518C17]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__paren__25518C17]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((0)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__f__state__2645B050]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__state__2645B050]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__state__2645B050]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((1)) FOR [state]
END


End;

/****** Object:  Default [DF__#__f__acces__2739D489]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__acces__2739D489]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__acces__2739D489]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__f__order__282DF8C2]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__order__282DF8C2]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__order__282DF8C2]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

SET IDENTITY_INSERT  [#__finder_taxonomy] ON
INSERT INTO [#__finder_taxonomy] ([id], [parent_id], [title], [state], [access], [ordering]) VALUES (1, 0, N'ROOT', 0, 0, 0);
SET IDENTITY_INSERT  [#__finder_taxonomy] OFF

/****** Object:  Table [#__finder_links_termsf]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_termsf]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__finder_links_termsf](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsf_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsf]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsf] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsf]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsf] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links_termse]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_termse]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__finder_links_termse](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termse_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termse]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termse] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termse]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termse] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links_termsd]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_termsd]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_termsd](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsd_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsd]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsd] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsd]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsd] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links_termsc]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_termsc]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_termsc](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsc_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsc]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsc] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsc]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsc] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links_termsb]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_termsb]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_termsb](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsb_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsb]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsb] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsb]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsb] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_links_termsa]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_termsa]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_termsa](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_termsa_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsa') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsa] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsa]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsa] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_links_terms9]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms9]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_terms9](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms9_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms9]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms9] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms9]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms9] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



/****** Object:  Table [#__finder_links_terms8]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms8]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_terms8](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms8_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms8]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms8] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms8]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms8] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_links_terms7]    Script Date: 03/16/2012 11:47:40 ******/

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms7]') AND type in (N'U'))
BEGIN
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms7](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms7_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms7]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms7] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms7]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms7] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_links_terms6]    Script Date: 03/16/2012 11:47:40 ******/

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms6]') AND type in (N'U'))
BEGIN
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms6](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms6_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms6]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms6] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms6]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms6] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_links_terms5]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms5]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_terms5](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms5_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms5]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms5] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms5]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms5] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links_terms4]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms4]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_terms4](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms4_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms4]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms4] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms4]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms4] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_links_terms3]    Script Date: 03/16/2012 11:47:40 ******/

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms3]') AND type in (N'U'))
BEGIN
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms3](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms3_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms3]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms3] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms3]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms3] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Table [#__finder_links_terms2]    Script Date: 03/16/2012 11:47:40 ******/
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms2]') AND type in (N'U'))
BEGIN

SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__finder_links_terms2](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms2_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms2]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms2] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms2]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms2] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links_terms1]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms1]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_terms1](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms1_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms1]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms1] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms1]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms1] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links_terms0]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms0]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links_terms0](
	[link_id] [bigint] NOT NULL,
	[term_id] [bigint] NOT NULL,
	[weight] [real] NOT NULL,
 CONSTRAINT [PK_#__finder_links_terms0_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC,
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms0]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms0] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms0]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms0] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Table [#__finder_links]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links](
	[link_id] [bigint] IDENTITY(1,1) NOT NULL,
	[url] [nvarchar](255) NOT NULL,
	[route] [nvarchar](255) NOT NULL,
	[title] [nvarchar](255) NULL,
	[description] [nvarchar](255) NULL,
	[indexdate] [datetime2](0) NOT NULL,
	[md5sum] [nvarchar](32) NULL,
	[published] [smallint] NOT NULL,
	[state] [int] NULL,
	[access] [int] NULL,
	[language] [nvarchar](8) NOT NULL,
	[publish_start_date] [datetime2](0) NOT NULL,
	[publish_end_date] [datetime2](0) NOT NULL,
	[start_date] [datetime2](0) NOT NULL,
	[end_date] [datetime2](0) NOT NULL,
	[list_price] [float] NOT NULL,
	[sale_price] [float] NOT NULL,
	[type_id] [int] NOT NULL,
	[object] [varbinary](max) NOT NULL,
 CONSTRAINT [PK_#__finder_links_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_md5] ON [#__finder_links] 
(
	[md5sum] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_published_list] ON [#__finder_links] 
(
	[published] ASC,
	[state] ASC,
	[access] ASC,
	[publish_start_date] ASC,
	[publish_end_date] ASC,
	[list_price] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_published_sale] ON [#__finder_links] 
(
	[published] ASC,
	[state] ASC,
	[access] ASC,
	[publish_start_date] ASC,
	[publish_end_date] ASC,
	[sale_price] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_title] ON [#__finder_links] 
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_type] ON [#__finder_links] 
(
	[type_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_url] ON [#__finder_links] 
(
	[url] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__f__title__08B54D69]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__title__08B54D69]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__title__08B54D69]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (NULL) FOR [title]
END


End;

/****** Object:  Default [DF__#__f__descr__09A971A2]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__descr__09A971A2]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__descr__09A971A2]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (NULL) FOR [description]
END


End;

/****** Object:  Default [DF__#__f__index__0A9D95DB]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__index__0A9D95DB]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__index__0A9D95DB]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (getdate()) FOR [indexdate]
END


End;

/****** Object:  Default [DF__#__f__md5su__0B91BA14]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__md5su__0B91BA14]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__md5su__0B91BA14]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (NULL) FOR [md5sum]
END


End;

/****** Object:  Default [DF__#__f__publi__0C85DE4D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__publi__0C85DE4D]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__publi__0C85DE4D]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((1)) FOR [published]
END


End;

/****** Object:  Default [DF__#__f__state__0D7A0286]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__state__0D7A0286]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__state__0D7A0286]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((1)) FOR [state]
END


End;

/****** Object:  Default [DF__#__f__acces__0E6E26BF]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__acces__0E6E26BF]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__acces__0E6E26BF]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__f__publi__0F624AF8]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__publi__0F624AF8]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__publi__0F624AF8]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (getdate()) FOR [publish_start_date]
END


End;

/****** Object:  Default [DF__#__f__publi__10566F31]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__publi__10566F31]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__publi__10566F31]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (getdate()) FOR [publish_end_date]
END


End;

/****** Object:  Default [DF__#__f__start__114A936A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__start__114A936A]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__start__114A936A]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (getdate()) FOR [start_date]
END


End;

/****** Object:  Default [DF__#__f__end_d__123EB7A3]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__end_d__123EB7A3]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__end_d__123EB7A3]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (getdate()) FOR [end_date]
END


End;

/****** Object:  Default [DF__#__f__list___1332DBDC]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__list___1332DBDC]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__list___1332DBDC]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((0)) FOR [list_price]
END


End;

/****** Object:  Default [DF__#__f__sale___14270015]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__sale___14270015]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__sale___14270015]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((0)) FOR [sale_price]
END


End;



/****** Object:  Table [#__finder_filters]    Script Date: 03/16/2012 11:47:41 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_filters]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_filters](
	[filter_id] [bigint] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[state] [smallint] NOT NULL,
	[created] [datetime2](0) NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime2](0) NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[map_count] [bigint] NOT NULL,
	[data] [nvarchar](max) NOT NULL,
	[params] [nvarchar](max) NULL,
 CONSTRAINT [PK_#__finder_filters_filter_id] PRIMARY KEY CLUSTERED 
(
	[filter_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__f__state__01142BA1]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__state__01142BA1]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__state__01142BA1]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((1)) FOR [state]
END


End;

/****** Object:  Default [DF__#__f__creat__02084FDA]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__creat__02084FDA]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__creat__02084FDA]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT (getdate()) FOR [created]
END


End;

/****** Object:  Default [DF__#__f__modif__02FC7413]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__modif__02FC7413]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__modif__02FC7413]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT (getdate()) FOR [modified]
END


End;

/****** Object:  Default [DF__#__f__modif__03F0984C]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__modif__03F0984C]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__modif__03F0984C]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__f__check__04E4BC85]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__check__04E4BC85]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__check__04E4BC85]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__f__check__05D8E0BE]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__check__05D8E0BE]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__check__05D8E0BE]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__f__map_c__06CD04F7]    Script Date: 03/16/2012 11:47:41 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__f__map_c__06CD04F7]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__f__map_c__06CD04F7]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((0)) FOR [map_count]
END


End;


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__languages]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__languages](
	[lang_id] [bigint] IDENTITY(2,1) NOT NULL,
	[lang_code] [nchar](7) NOT NULL,
	[title] [nvarchar](50) NOT NULL,
	[title_native] [nvarchar](50) NOT NULL,
	[sef] [nvarchar](50) NOT NULL,
	[image] [nvarchar](50) NOT NULL,
	[description] [nvarchar](512) NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[sitename] [nvarchar](1024) NOT NULL,
	[published] [int] NOT NULL,
	[ordering] [int] NOT NULL,
 CONSTRAINT [PK_#__languages_lang_id] PRIMARY KEY CLUSTERED 
(
	[lang_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__languages]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_ordering] ON [#__languages] 
(
	[ordering] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__l__siten__3A4CA8FD]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__l__siten__3A4CA8FD]') AND parent_object_id = OBJECT_ID(N'[#__languages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__l__siten__3A4CA8FD]') AND type = 'D')
BEGIN
ALTER TABLE [#__languages] ADD  DEFAULT (N'') FOR [sitename]
END


End;

/****** Object:  Default [DF__#__l__publi__3B40CD36]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__l__publi__3B40CD36]') AND parent_object_id = OBJECT_ID(N'[#__languages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__l__publi__3B40CD36]') AND type = 'D')
BEGIN
ALTER TABLE [#__languages] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__l__order__3C34F16F]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__l__order__3C34F16F]') AND parent_object_id = OBJECT_ID(N'[#__languages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__l__order__3C34F16F]') AND type = 'D')
BEGIN
ALTER TABLE [#__languages] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

SET IDENTITY_INSERT [#__languages] ON
INSERT INTO [#__languages] ([lang_id], [lang_code], [title], [title_native], [sef], [image], [description], [metakey], [metadesc], [sitename], [published], [ordering]) VALUES (1, N'en-GB  ', N'English (UK)', N'English (UK)', N'en', N'en', N'', N'', N'', N'', 1, 1);
SET IDENTITY_INSERT [#__languages] OFF

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__menu]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__menu](
	[id] [int] IDENTITY(110,1) NOT NULL,
	[menutype] [nvarchar](24) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[note] [nvarchar](255) NOT NULL,
	[path] [nvarchar](1024) NOT NULL,
	[link] [nvarchar](1024) NOT NULL,
	[type] [nvarchar](16) NOT NULL,
	[published] [smallint] NOT NULL,
	[parent_id] [bigint] NOT NULL,
	[level] [bigint] NOT NULL,
	[component_id] [bigint] NOT NULL,
	[ordering] [int] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
	[browserNav] [smallint] NOT NULL,
	[access] [bigint] NOT NULL,
	[img] [nvarchar](255) NOT NULL,
	[template_style_id] [bigint] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[lft] [int] NOT NULL,
	[rgt] [int] NOT NULL,
	[home] [tinyint] NOT NULL,
	[language] [nchar](7) NOT NULL,
	[client_id] [smallint] NOT NULL,
 CONSTRAINT [PK_#__menu_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_alias] ON [#__menu] 
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_componentid] ON [#__menu] 
(
	[component_id] ASC,
	[menutype] ASC,
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__menu] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__menu] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_menutype] ON [#__menu] 
(
	[menutype] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__me__note__3E1D39E1]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__me__note__3E1D39E1]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__me__note__3E1D39E1]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT (N'') FOR [note]
END


End;

/****** Object:  Default [DF__#__m__publi__3F115E1A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__publi__3F115E1A]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__publi__3F115E1A]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__m__paren__40058253]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__paren__40058253]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__paren__40058253]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((1)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__m__level__40F9A68C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__level__40F9A68C]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__level__40F9A68C]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [level]
END


End;

/****** Object:  Default [DF__#__m__compo__41EDCAC5]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__compo__41EDCAC5]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__compo__41EDCAC5]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [component_id]
END


End;


/****** Object:  Default [DF__#__m__order__42E1EEFE]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__order__42E1EEFE]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__order__42E1EEFE]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__m__check__43D61337]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__check__43D61337]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__check__43D61337]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__m__check__44CA3770]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__check__44CA3770]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__check__44CA3770]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__m__brows__45BE5BA9]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__brows__45BE5BA9]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__brows__45BE5BA9]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [browserNav]
END


End;

/****** Object:  Default [DF__#__m__acces__46B27FE2]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__acces__46B27FE2]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__m__templ__47A6A41B]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__templ__47A6A41B]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__templ__47A6A41B]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [template_style_id]
END


End;

/****** Object:  Default [DF__#__men__lft__489AC854]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__men__lft__489AC854]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__men__lft__489AC854]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__men__rgt__498EEC8D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__men__rgt__498EEC8D]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__men__rgt__498EEC8D]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [rgt]
END


End;

/****** Object:  Default [DF__#__me__home__4A8310C6]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__me__home__4A8310C6]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__me__home__4A8310C6]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [home]
END


End;

/****** Object:  Default [DF__#__m__langu__4B7734FF]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__langu__4B7734FF]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__langu__4B7734FF]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Default [DF__#__m__clien__4C6B5938]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__clien__4C6B5938]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__clien__4C6B5938]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

SET IDENTITY_INSERT [#__menu] ON;
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '1900-01-01T00:00:00.000', 0, 0, '', 0, '', 0, 61, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(2, 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 0, 1, 1, 4, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:banners', 0, '', 15, 24, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(3, 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 0, 2, 2, 4, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:banners', 0, '', 16, 17, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(4, 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 0, 2, 2, 6, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:banners-cat', 0, '', 18, 19, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(5, 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 0, 2, 2, 4, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:banners-clients', 0, '', 20, 21, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(6, 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 0, 2, 2, 4, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:banners-tracks', 0, '', 22, 23, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(7, 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 0, 1, 1, 8, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:contact', 0, '', 25, 30, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(8, 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 0, 7, 2, 8, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:contact', 0, '', 26, 27, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(9, 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 0, 7, 2, 6, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:contact-cat', 0, '', 28, 29, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(10, 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 0, 1, 1, 15, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:messages', 0, '', 31, 36, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(11, 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 0, 10, 2, 15, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:messages-add', 0, '', 32, 33, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(12, 'menu', 'com_messages_read', 'Read Private Message', '', 'Messaging/Read Private Message', 'index.php?option=com_messages', 'component', 0, 10, 2, 15, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:messages-read', 0, '', 34, 35, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(13, 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 1, 1, 17, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:newsfeeds', 0, '', 37, 42, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(14, 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 13, 2, 17, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:newsfeeds', 0, '', 38, 39, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(15, 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 0, 13, 2, 6, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:newsfeeds-cat', 0, '', 40, 41, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(16, 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 0, 1, 1, 24, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:redirect', 0, '', 53, 54, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(17, 'menu', 'com_search', 'Basic Search', '', 'Basic Search', 'index.php?option=com_search', 'component', 0, 1, 1, 19, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:search', 0, '', 45, 46, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(18, 'menu', 'com_weblinks', 'Weblinks', '', 'Weblinks', 'index.php?option=com_weblinks', 'component', 0, 1, 1, 21, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:weblinks', 0, '', 47, 52, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(19, 'menu', 'com_weblinks_links', 'Links', '', 'Weblinks/Links', 'index.php?option=com_weblinks', 'component', 0, 18, 2, 21, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:weblinks', 0, '', 48, 49, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(20, 'menu', 'com_weblinks_categories', 'Categories', '', 'Weblinks/Categories', 'index.php?option=com_categories&extension=com_weblinks', 'component', 0, 18, 2, 6, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:weblinks-cat', 0, '', 50, 51, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(21, 'menu', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 0, 1, 1, 27, 0, 0, '1900-01-01T00:00:00.000', 0, 0, 'class:finder', 0, '', 43, 44, 0, '*', 1);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=category&layout=blog&id=9', 'component', 1, 1, 1, 22, 0, 0, '1900-01-01T00:00:00.000', 0, 1, '', 0, '{"layout_type":"blog","show_category_title":"","show_description":"","show_description_image":"","maxLevel":"","show_empty_categories":"","show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"","page_subheading":"","num_leading_articles":"4","num_intro_articles":"0","num_columns":"0","num_links":"4","multi_column_order":"1","show_subcategory_content":"","orderby_pri":"","orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":1,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 55, 56, 1, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(102, 'bottommenu', 'Author Login', 'login', '', 'login', 'index.php?option=com_users&view=login', 'component', 1, 1, 1, 25, 0, 0, '1900-01-01T00:00:00.000', 0, 1, '', 0, '{"login_redirect_url":"index.php?Itemid=101","logindescription_show":"1","login_description":"","login_image":"","logout_redirect_url":"","logoutdescription_show":"1","logout_description":"","logout_image":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 59, 60, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(103, 'authormenu', 'Change Password', 'change-password', '', 'change-password', 'index.php?option=com_users&view=profile&layout=edit', 'component', 1, 1, 1, 25, 0, 0, '1900-01-01T00:00:00.000', 0, 2, '', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 1, 2, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(104, 'authormenu', 'Create a Post', 'create-a-post', '', 'create-a-post', 'index.php?option=com_content&view=form&layout=edit', 'component', 1, 1, 1, 22, 0, 0, '1900-01-01T00:00:00.000', 0, 3, '', 0, '{"enable_category":"1","catid":"9","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 3, 4, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(105, 'authormenu', 'Add to Blog Roll', 'add-to-blog-roll', '', 'add-to-blog-roll', 'index.php?option=com_weblinks&view=form&layout=edit', 'component', 1, 1, 1, 21, 0, 0, '1900-01-01T00:00:00.000', 0, 3, '', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 5, 6, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(106, 'authormenu', 'Site Administrator', '2012-01-04-15-46-42', '', '2012-01-04-15-46-42', 'administrator', 'url', 1, 1, 1, 0, 0, 0, '1900-01-01T00:00:00.000', 1, 3, '', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1}', 11, 12, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(107, 'authormenu', 'Log out', 'log-out', '', 'log-out', 'index.php?option=com_users&view=login', 'component', 1, 1, 1, 25, 0, 0, '1900-01-01T00:00:00.000', 0, 1, '', 0, '{"login_redirect_url":"","logindescription_show":"1","login_description":"","login_image":"","logout_redirect_url":"","logoutdescription_show":"1","logout_description":"","logout_image":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 13, 14, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(108, 'mainmenu', 'About', 'about', '', 'about', 'index.php?option=com_content&view=article&id=1', 'component', 1, 1, 1, 22, 0, 0, '1900-01-01T00:00:00.000', 0, 1, '', 0, '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 57, 58, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(109, 'authormenu', 'Working on Your Site', 'working-on-your-site', '', 'working-on-your-site', 'index.php?option=com_content&view=article&id=2', 'component', 1, 1, 1, 22, 0, 0, '1900-01-01T00:00:00.000', 0, 1, '', 0, '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 9, 10, 0, '*', 0);
INSERT INTO #__menu ([id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id], [ordering], [checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id]) VALUES(110, 'authormenu', 'Edit Blog Roll', 'edit-blog-roll', '', 'edit-blog-roll', 'index.php?option=com_weblinks&view=category&id=8', 'component', 1, 1, 1, 21, 0, 0, '1900-01-01T00:00:00.000', 0, 1, '', 0, '{"show_category_title":"","show_description":"","show_description_image":"","maxLevel":"","show_empty_categories":"","show_subcat_desc":"","show_cat_num_links":"","show_pagination_limit":"","show_headings":"","show_link_description":"","show_link_hits":"","show_pagination":"","show_pagination_results":"","show_feed_link":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 7, 8, 0, '*', 0);

SET IDENTITY_INSERT [#__menu] OFF;

/****** Object:  Table [#__menu_types]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__menu_types]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__menu_types](
	[id] [bigint] IDENTITY(2,1) NOT NULL,
	[menutype] [nvarchar](24) NOT NULL,
	[title] [nvarchar](48) NOT NULL,
	[description] [nvarchar](255) NOT NULL,
 CONSTRAINT [PK_#__menu_types_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__m__descr__4E53A1AA]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__menu_types]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu_types] ADD  DEFAULT (N'') FOR [description]
END

End;

SET IDENTITY_INSERT [#__menu_types] ON;

INSERT [dbo].[#__menu_types] ([id], [menutype], [title], [description]) VALUES (1, N'mainmenu', N'Main Menu', N'The main menu for the site');
INSERT [dbo].[#__menu_types] ([id], [menutype], [title], [description]) VALUES (2, N'authormenu', N'Author Menu', N'');
INSERT [dbo].[#__menu_types] ([id], [menutype], [title], [description]) VALUES (3, N'bottommenu', N'Bottom Menu', N'');
SET IDENTITY_INSERT [#__menu_types] OFF;


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__messages]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__messages](
	[message_id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id_from] [bigint] NOT NULL,
	[user_id_to] [bigint] NOT NULL,
	[folder_id] [tinyint] NOT NULL,
	[date_time] [datetime2](0) NOT NULL,
	[state] [smallint] NOT NULL,
	[priority] [tinyint] NOT NULL,
	[subject] [nvarchar](255) NOT NULL,
	[message] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__messages_message_id] PRIMARY KEY CLUSTERED 
(
	[message_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__messages]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [useridto_state] ON [#__messages] 
(
	[user_id_to] ASC,
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__m__user___503BEA1C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__user___503BEA1C]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__user___503BEA1C]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [user_id_from]
END


End;

/****** Object:  Default [DF__#__m__user___51300E55]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__user___51300E55]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__user___51300E55]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [user_id_to]
END


End;

/****** Object:  Default [DF__#__m__folde__5224328E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__folde__5224328E]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__folde__5224328E]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [folder_id]
END


End;

/****** Object:  Default [DF__#__m__date___531856C7]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__folde__5224328E]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT (getdate()) FOR [date_time]
END


End;

/****** Object:  Default [DF__#__m__folde__5224328E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__prior__5224328E]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__prior__5224328E]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__m__prior__55009F39]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__prior__55009F39]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__prior__55009F39]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [priority]
END


End;

/****** Object:  Default [DF__#__m__subje__55F4C372]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__subje__55F4C372]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__subje__55F4C372]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT (N'') FOR [subject]
END


End;

/****** Object:  Table [#__messages_cfg]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__messages_cfg]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__messages_cfg](
	[user_id] [bigint] NOT NULL,
	[cfg_name] [nvarchar](100) NOT NULL,
	[cfg_value] [nvarchar](255) NOT NULL
)
END;
/****** Object:  Default [DF__#__m__user___57DD0BE4]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__user___57DD0BE4]') AND parent_object_id = OBJECT_ID(N'[#__messages_cfg]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__user___57DD0BE4]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages_cfg] ADD  DEFAULT ((0)) FOR [user_id]
END


End;

/****** Object:  Default [DF__#__m__cfg_n__58D1301D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__cfg_n__58D1301D]') AND parent_object_id = OBJECT_ID(N'[#__messages_cfg]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__cfg_n__58D1301D]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages_cfg] ADD  DEFAULT (N'') FOR [cfg_name]
END


End;

/****** Object:  Default [DF__#__m__cfg_v__59C55456]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__cfg_v__59C55456]') AND parent_object_id = OBJECT_ID(N'[#__messages_cfg]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__cfg_v__59C55456]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages_cfg] ADD  DEFAULT (N'') FOR [cfg_value]
END


End;

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__modules]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__modules](
	[id] [int] IDENTITY(84,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[note] [nvarchar](255) NOT NULL,
	[content] [nvarchar](max) NOT NULL,
	[ordering] [int] NOT NULL,
	[position] [nvarchar](50) NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[publish_up] [datetime2](0) NOT NULL,
	[publish_down] [datetime2](0) NOT NULL,
	[published] [smallint] NOT NULL,
	[module] [nvarchar](50) NULL,
	[access] [bigint] NOT NULL,
	[showtitle] [tinyint] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[client_id] [smallint] NOT NULL,
	[language] [nchar](7) NOT NULL,
 CONSTRAINT [PK_#__modules_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__modules]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__modules] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__modules]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [newsfeeds] ON [#__modules] 
(
	[module] ASC,
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__modules]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [published] ON [#__modules] 
(
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Default [DF__#__m__title__5BAD9CC8]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__title__5BAD9CC8]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__title__5BAD9CC8]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (N'') FOR [title]
END


End;

/****** Object:  Default [DF__#__mo__note__5CA1C101]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__mo__note__5CA1C101]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__mo__note__5CA1C101]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (N'') FOR [note]
END


End;

/****** Object:  Default [DF__#__m__order__5D95E53A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__order__5D95E53A]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__order__5D95E53A]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__m__posit__5E8A0973]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__posit__5E8A0973]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__posit__5E8A0973]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (N'') FOR [position]
END


End;

/****** Object:  Default [DF__#__m__check__5F7E2DAC]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__check__5F7E2DAC]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__check__5F7E2DAC]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__m__check__607251E5]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__check__607251E5]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__check__607251E5]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__m__publi__6166761E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__publi__6166761E]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__publi__6166761E]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (getdate()) FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__m__publi__625A9A57]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__publi__625A9A57]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__publi__625A9A57]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (getdate()) FOR [publish_down]
END


End;

/****** Object:  Default [DF__#__m__publi__634EBE90]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__publi__634EBE90]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__publi__634EBE90]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__m__modul__6442E2C9]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__modul__6442E2C9]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__modul__6442E2C9]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (NULL) FOR [module]
END


End;

/****** Object:  Default [DF__#__m__acces__65370702]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__acces__65370702]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__acces__65370702]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__m__showt__662B2B3B]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__showt__662B2B3B]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__showt__662B2B3B]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((1)) FOR [showtitle]
END


End;

/****** Object:  Default [DF__#__m__clien__671F4F74]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__clien__671F4F74]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__clien__671F4F74]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

SET IDENTITY_INSERT [#__modules] ON;

INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES
(1, 'Main Menu', '', '', 1, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_menu', 1, 0, '{"menutype":"mainmenu","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"_:default","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(2, 'Login', '', '', 1, 'login', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_login', 1, 1, '', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(3, 'Popular Articles', '', '', 3, 'cpanel', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(4, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(8, 'Toolbar', '', '', 1, 'toolbar', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_toolbar', 3, 1, '', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(9, 'Quick Icons', '', '', 1, 'icon', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_quickicon', 3, 1, '', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(10, 'Logged-in Users', '', '', 2, 'cpanel', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(12, 'Admin Menu', '', '', 1, 'menu', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(13, 'Admin Submenu', '', '', 1, 'submenu', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_submenu', 3, 1, '', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(14, 'User Status', '', '', 2, 'status', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_status', 3, 1, '', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(15, 'Title', '', '', 1, 'title', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_title', 3, 1, '', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(16, 'Login Form', '', '', 7, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 0, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(17, 'Breadcrumbs', '', '', 1, 'position-2', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"Home","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(79, 'Multilanguage status', '', '', 1, 'status', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(80, 'Author Menu', '', '', 1, 'position-1', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_menu', 3, 0, '{"menutype":"authormenu","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(81, 'Blog Roll', '', '', 3, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_weblinks', 1, 1, '{"catid":"8","count":"5","ordering":"title","direction":"asc","target":"1","description":"0","hits":"0","count_clicks":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(82, 'Syndication', '', '', 6, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_syndicate', 1, 0, '{"display_text":1,"text":"My Blog","format":"rss","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(83, 'Archived Articles', '', '', 4, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_articles_archive', 1, 1, '{"count":"10","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(84, 'Most Read Posts', '', '', 5, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_articles_popular', 1, 1, '{"catid":["9"],"count":"5","show_front":"1","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(85, 'Older Posts', '', '', 2, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_articles_category', 1, 1, '{"mode":"normal","show_on_article_page":"1","show_front":"show","count":"0","category_filtering_type":"1","catid":["9"],"show_child_category_articles":"0","levels":"1","author_filtering_type":"1","created_by":[""],"author_alias_filtering_type":"1","created_by_alias":[""],"excluded_articles":"","date_filtering":"off","date_field":"a.created","start_date_range":"","end_date_range":"","relative_date":"30","article_ordering":"a.title","article_ordering_direction":"ASC","article_grouping":"month_year","article_grouping_direction":"krsort","month_year_format":"F Y","item_heading":"4","link_titles":"1","show_date":"1","show_date_field":"created","show_date_format":"Y-m-d H:i:s","show_category":"0","show_hits":"0","show_author":"0","show_introtext":"0","introtext_limit":"100","show_readmore":"0","show_readmore_title":"1","readmore_limit":"15","layout":"_:default","moduleclass_sfx":"","owncache":"1","cache_time":"900"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(86, 'Bottom Menu', '', '', 8, 'position-7', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_menu', 1, 0, '{"menutype":"bottommenu","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(87, 'Search', '', '', 1, 'position-0', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_search', 1, 1, '{"label":"","width":"20","text":"","button":"","button_pos":"right","imagebutton":"","button_text":"","opensearch":"1","opensearch_title":"","set_itemid":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*');
INSERT INTO [#__modules] ([id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language]) VALUES(88, 'Header', '', '', 1, 'position-1', 0, '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000', 1, 'mod_custom', 1, 0, '{"prepare_content":"1","backgroundimage":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}', 0, '*');

SET IDENTITY_INSERT [#__modules] OFF;

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__modules_menu]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__modules_menu](
	[moduleid] [int] NOT NULL,
	[menuid] [int] NOT NULL,
 CONSTRAINT [PK_#__modules_menu_moduleid] PRIMARY KEY CLUSTERED 
(
	[moduleid] ASC,
	[menuid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__m__modul__690797E6]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__modul__690797E6]') AND parent_object_id = OBJECT_ID(N'[#__modules_menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__modul__690797E6]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules_menu] ADD  DEFAULT ((0)) FOR [moduleid]
END


End;

/****** Object:  Default [DF__#__m__menui__69FBBC1F]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__m__menui__69FBBC1F]') AND parent_object_id = OBJECT_ID(N'[#__modules_menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__m__menui__69FBBC1F]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules_menu] ADD  DEFAULT ((0)) FOR [menuid]
END


End;

INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (1, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (2, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (3, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (4, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (6, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (7, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (8, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (9, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (10, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (12, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (13, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (14, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (15, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (16, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (17, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (79, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (80, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (81, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (82, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (83, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (84, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (85, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (86, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (87, 0);
INSERT INTO[#__modules_menu] ([moduleid], [menuid]) VALUES (88, 0);


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__newsfeeds](
	[catid] [int] NOT NULL,
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[link] [nvarchar](200) NOT NULL,
	[filename] [nvarchar](200) NULL,
	[published] [smallint] NOT NULL,
	[numarticles] [bigint] NOT NULL,
	[cache_time] [bigint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[ordering] [int] NOT NULL,
	[rtl] [smallint] NOT NULL,
	[access] [bigint] NOT NULL,
	[language] [nchar](7) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[created] [datetime2](0) NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime2](0) NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime2](0) NOT NULL,
	[publish_down] [datetime2](0) NOT NULL,
 CONSTRAINT [PK_#__newsfeeds_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_access] ON [#__newsfeeds] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__newsfeeds] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__newsfeeds] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__newsfeeds] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__newsfeeds] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__newsfeeds] 
(
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__newsfeeds] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Default [DF__#__n__catid__6BE40491]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__catid__6BE40491]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__catid__6BE40491]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__ne__name__6CD828CA]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ne__name__6CD828CA]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ne__name__6CD828CA]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__n__alias__6DCC4D03]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__alias__6DCC4D03]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__alias__6DCC4D03]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__ne__link__6EC0713C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ne__link__6EC0713C]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ne__link__6EC0713C]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [link]
END


End;

/****** Object:  Default [DF__#__n__filen__6FB49575]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__filen__6FB49575]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__filen__6FB49575]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (NULL) FOR [filename]
END


End;

/****** Object:  Default [DF__#__n__publi__70A8B9AE]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__publi__70A8B9AE]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__publi__70A8B9AE]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__n__numar__719CDDE7]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__numar__719CDDE7]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__numar__719CDDE7]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((1)) FOR [numarticles]
END


End;

/****** Object:  Default [DF__#__n__cache__72910220]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__cache__72910220]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__cache__72910220]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((3600)) FOR [cache_time]
END


End;

/****** Object:  Default [DF__#__n__check__73852659]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__check__73852659]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__check__73852659]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__n__check__74794A92]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__check__74794A92]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__check__74794A92]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__n__order__756D6ECB]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__order__756D6ECB]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__order__756D6ECB]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__new__rtl__76619304]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__new__rtl__76619304]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__new__rtl__76619304]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [rtl]
END


End;
/****** Object:  Default [DF__#__n__acces__7755B73D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__acces__7755B73D]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__acces__7755B73D]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__n__langu__7849DB76]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__langu__7849DB76]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__langu__7849DB76]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Default [DF__#__n__creat__793DFFAF]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__creat__793DFFAF]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__creat__793DFFAF]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (getdate()) FOR [created]
END


End;

/****** Object:  Default [DF__#__n__creat__7A3223E8]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__creat__7A3223E8]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__creat__7A3223E8]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [created_by]
END


End;

/****** Object:  Default [DF__#__n__creat__7B264821]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__creat__7B264821]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__creat__7B264821]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [created_by_alias]
END


End;

/****** Object:  Default [DF__#__n__modif__7C1A6C5A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__modif__7C1A6C5A]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__modif__7C1A6C5A]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (getdate()) FOR [modified]
END


End;

/****** Object:  Default [DF__#__n__modif__7D0E9093]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__modif__7D0E9093]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__modif__7D0E9093]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__n__publi__7E02B4CC]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__publi__7E02B4CC]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__publi__7E02B4CC]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (getdate()) FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__n__publi__7EF6D905]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__n__publi__7EF6D905]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__n__publi__7EF6D905]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (getdate()) FOR [publish_down]
END


End;

/****** Object:  Table [#__overrider]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__overrider]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__overrider](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[constant] [nvarchar](255) NOT NULL,
	[string] [nvarchar](max) NOT NULL,
	[file] [nvarchar](255) NOT NULL,
 CONSTRAINT [PK_#__overrider_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Table [#__redirect_links]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__redirect_links]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__redirect_links](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[old_url] [nvarchar](255) NOT NULL,
	[new_url] [nvarchar](255) NOT NULL,
	[referer] [nvarchar](150) NOT NULL,
	[comment] [nvarchar](255) NOT NULL,
	[published] [smallint] NOT NULL,
	[created_date] [datetime2](0) NOT NULL,
	[modified_date] [datetime2](0) NOT NULL,
 CONSTRAINT [PK_#__redirect_links_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__redirect_links]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_link_modifed] ON [#__redirect_links] 
(
	[modified_date] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


/****** Object:  Default [DF__#__r__creat__01D345B0]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__r__creat__01D345B0]') AND parent_object_id = OBJECT_ID(N'[#__redirect_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__r__creat__01D345B0]') AND type = 'D')
BEGIN
ALTER TABLE [#__redirect_links] ADD  DEFAULT (getdate()) FOR [created_date]
END


End;

/****** Object:  Default [DF__#__r__modif__02C769E9]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__r__modif__02C769E9]') AND parent_object_id = OBJECT_ID(N'[#__redirect_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__r__modif__02C769E9]') AND type = 'D')
BEGIN
ALTER TABLE [#__redirect_links] ADD  DEFAULT (getdate()) FOR [modified_date]
END


End;

SET IDENTITY_INSERT [#__redirect_links] ON;

INSERT INTO [#__redirect_links] ([id], [old_url], [new_url], [referer], [comment], [published], [created_date], [modified_date]) VALUES
(1, 'http://localhost/trunk123111/index.php/create-a-post/login', '', 'http://localhost/trunk123111/index.php/login', '', 0, '2012-01-04 15:48:49', '1900-01-01T00:00:00.000');
INSERT INTO [#__redirect_links] ([id], [old_url], [new_url], [referer], [comment], [published], [created_date], [modified_date]) VALUES(2, 'http://localhost/trunk123111/index.php/create-an-article', '', 'http://localhost/trunk123111/index.php/creating-your-site', '', 0, '2012-01-05 02:01:07', '1900-01-01T00:00:00.000');
INSERT INTO [#__redirect_links] ([id], [old_url], [new_url], [referer], [comment], [published], [created_date], [modified_date]) VALUES(3, 'http://localhost/joomla-cms/index.php/using-joomla', '', 'http://localhost/joomla-cms/', '', 0, '2012-01-17 06:21:35', '1900-01-01T00:00:00.000');
SET IDENTITY_INSERT [#__redirect_links] OFF;

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__schemas]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__schemas](
	[extension_id] [int] NOT NULL,
	[version_id] [nvarchar](20) NOT NULL,
 CONSTRAINT [PK_#__schemas_extension_id] PRIMARY KEY CLUSTERED 
(
	[extension_id] ASC,
	[version_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

INSERT INTO[#__schemas] ([extension_id], [version_id]) VALUES (700, N'2.5.0-2011-12-27');
INSERT INTO[#__schemas] ([extension_id], [version_id]) VALUES (700, N'2.5.0-2012-01-14');

/****** Object:  Table [#__session]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__session]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__session](
	[session_id] [nvarchar](200) NOT NULL,
	[client_id] [tinyint] NOT NULL,
	[guest] [tinyint] NULL,
	[time] [nvarchar](14) NULL,
	[data] [nvarchar](max) NULL,
	[userid] [int] NULL,
	[username] [nvarchar](150) NULL,
	[usertype] [nvarchar](50) NULL,
 CONSTRAINT [PK_#__session_session_id] PRIMARY KEY CLUSTERED 
(
	[session_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__session]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [time] ON [#__session] 
(
	[time] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__session]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [userid] ON [#__session] 
(
	[userid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__session]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [whosonline] ON [#__session] 
(
	[guest] ASC,
	[usertype] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



/****** Object:  Default [DF__#__s__sessi__05A3D694]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__s__sessi__05A3D694]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__s__sessi__05A3D694]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [session_id]
END


End;

/****** Object:  Default [DF__#__s__clien__0697FACD]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__s__clien__0697FACD]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__s__clien__0697FACD]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

/****** Object:  Default [DF__#__s__guest__078C1F06]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__s__guest__078C1F06]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__s__guest__078C1F06]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT ((1)) FOR [guest]
END


End;

/****** Object:  Default [DF__#__se__time__0880433F]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__se__time__0880433F]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__se__time__0880433F]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [time]
END


End;

/****** Object:  Default [DF__#__s__useri__09746778]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__s__useri__09746778]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__s__useri__09746778]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT ((0)) FOR [userid]
END


End;

/****** Object:  Default [DF__#__s__usern__0A688BB1]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__s__usern__0A688BB1]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__s__usern__0A688BB1]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [username]
END


End;

/****** Object:  Default [DF__#__s__usert__0B5CAFEA]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__s__usert__0B5CAFEA]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__s__usert__0B5CAFEA]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [usertype]
END


End;


INSERT [#__session] ([session_id], [client_id], [guest], [time], [data], [userid], [username], [usertype]) VALUES  
('t8ptu9b35thpkrft5crb8ssea2', 0, 0, '1326805166', '__default|a:9:{s:15:"session.counter";i:8;s:19:"session.timer.start";i:1326804893;s:18:"session.timer.last";i:1326805095;s:17:"session.timer.now";i:1326805165;s:22:"session.client.browser";s:101:"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.75 Safari/535.7";s:8:"registry";O:9:"JRegistry":1:{s:7:"\0*\0data";O:8:"stdClass":1:{s:5:"users";O:8:"stdClass":1:{s:5:"login";O:8:"stdClass":1:{s:4:"form";O:8:"stdClass":2:{s:4:"data";a:0:{}s:6:"return";s:20:"index.php?Itemid=101";}}}}}s:4:"user";O:5:"JUser":23:{s:9:"\0*\0isRoot";b:1;s:2:"id";s:2:"42";s:4:"name";s:10:"Super User";s:8:"username";s:5:"admin";s:5:"email";s:14:"admin@fake.com";s:8:"password";s:65:"dda41476e1c43ddd832e1b9afc3d85ea:UXOJIAMTh3meI7iICS6DoCWCe9QOj3zL";s:14:"password_clear";s:0:"";s:8:"usertype";s:10:"deprecated";s:5:"block";s:1:"0";s:9:"sendEmail";s:1:"1";s:12:"registerDate";s:19:"2012-01-17 06:21:22";s:13:"lastvisitDate";s:19:"2012-01-17 12:06:14";s:10:"activation";s:1:"0";s:6:"params";s:0:"";s:6:"groups";a:1:{i:8;s:1:"8";}s:5:"guest";i:0;s:10:"\0*\0_params";O:9:"JRegistry":1:{s:7:"\0*\0data";O:8:"stdClass":0:{}}s:14:"\0*\0_authGroups";a:2:{i:0;i:1;i:1;i:8;}s:14:"\0*\0_authLevels";a:4:{i:0;i:1;i:1;i:1;i:2;i:2;i:3;i:3;}s:15:"\0*\0_authActions";N;s:12:"\0*\0_errorMsg";N;s:10:"\0*\0_errors";a:0:{}s:3:"aid";i:0;}s:13:"session.token";s:32:"2bb9ce8553ba94e056ddebe737a07442";s:16:"com_mailto.links";a:4:{s:40:"053719df8b8aa0d3baddf6fdf1ec7250b4a13d9e";O:8:"stdClass":2:{s:4:"link";s:60:"http://localhost/joomla-cms/index.php/3-welcome-to-your-blog";s:6:"expiry";i:1326804911;}s:40:"79c1ab4c4d9435d020344c88ad774d2c06191439";O:8:"stdClass":2:{s:4:"link";s:60:"http://localhost/joomla-cms/index.php/4-about-your-home-page";s:6:"expiry";i:1326804911;}s:40:"b7678125768750623235602ae216cbe44e62ad01";O:8:"stdClass":2:{s:4:"link";s:52:"http://localhost/joomla-cms/index.php/5-your-modules";s:6:"expiry";i:1326804911;}s:40:"faff260ed0a5713401d6b4cfba18037aee9cdfbf";O:8:"stdClass":2:{s:4:"link";s:58:"http://localhost/joomla-cms/index.php/working-on-your-site";s:6:"expiry";i:1326805165;}}}', 42, 'admin', '');
INSERT [#__session] ([session_id], [client_id], [guest], [time], [data], [userid], [username], [usertype]) VALUES ('ufq2f2u61nbm1782uufa0t3o31', 1, 0, '1326805336', '__default|a:8:{s:15:"session.counter";i:280;s:19:"session.timer.start";i:1326781285;s:18:"session.timer.last";i:1326805161;s:17:"session.timer.now";i:1326805334;s:22:"session.client.browser";s:101:"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.75 Safari/535.7";s:8:"registry";O:9:"JRegistry":1:{s:7:"\0*\0data";O:8:"stdClass":8:{s:11:"application";O:8:"stdClass":1:{s:4:"lang";s:0:"";}s:13:"com_installer";O:8:"stdClass":2:{s:7:"message";s:0:"";s:17:"extension_message";s:0:"";}s:13:"com_languages";O:8:"stdClass":1:{s:9:"installed";O:8:"stdClass":1:{s:8:"ordercol";s:6:"a.name";}}s:13:"com_templates";O:8:"stdClass":2:{s:6:"styles";O:8:"stdClass":1:{s:10:"limitstart";i:0;}s:4:"edit";O:8:"stdClass":1:{s:5:"style";O:8:"stdClass":2:{s:2:"id";a:0:{}s:4:"data";N;}}}s:11:"com_modules";O:8:"stdClass":4:{s:7:"modules";O:8:"stdClass":4:{s:6:"filter";O:8:"stdClass":8:{s:18:"client_id_previous";i:0;s:6:"search";s:0:"";s:6:"access";i:0;s:5:"state";s:0:"";s:8:"position";s:0:"";s:6:"module";s:0:"";s:9:"client_id";i:0;s:8:"language";s:0:"";}s:10:"limitstart";s:1:"0";s:8:"ordercol";s:8:"ordering";s:9:"orderdirn";s:3:"asc";}s:4:"edit";O:8:"stdClass":1:{s:6:"module";O:8:"stdClass":2:{s:2:"id";a:0:{}s:4:"data";N;}}s:3:"add";O:8:"stdClass":1:{s:6:"module";O:8:"stdClass":2:{s:12:"extension_id";N;s:6:"params";N;}}s:9:"positions";O:8:"stdClass":4:{s:6:"filter";O:8:"stdClass":4:{s:6:"search";s:0:"";s:5:"state";s:0:"";s:8:"template";s:0:"";s:4:"type";s:0:"";}s:10:"limitstart";s:2:"20";s:8:"ordercol";s:5:"value";s:9:"orderdirn";s:3:"asc";}}s:6:"global";O:8:"stdClass":1:{s:4:"list";O:8:"stdClass":1:{s:5:"limit";s:2:"20";}}s:9:"com_menus";O:8:"stdClass":2:{s:5:"items";O:8:"stdClass":6:{s:6:"filter";O:8:"stdClass":4:{s:8:"menutype";s:8:"mainmenu";s:6:"access";i:0;s:5:"level";i:0;s:8:"language";s:0:"";}s:10:"limitstart";i:0;s:6:"search";s:0:"";s:9:"published";s:0:"";s:8:"ordercol";s:5:"a.lft";s:9:"orderdirn";s:3:"asc";}s:4:"edit";O:8:"stdClass":1:{s:4:"item";O:8:"stdClass":4:{s:2:"id";a:0:{}s:4:"data";N;s:4:"type";N;s:4:"link";N;}}}s:11:"com_content";O:8:"stdClass":1:{s:4:"edit";O:8:"stdClass":1:{s:7:"article";O:8:"stdClass":2:{s:2:"id";a:1:{i:0;i:2;}s:4:"data";N;}}}}}s:4:"user";O:5:"JUser":23:{s:9:"\0*\0isRoot";b:1;s:2:"id";s:2:"42";s:4:"name";s:10:"Super User";s:8:"username";s:5:"admin";s:5:"email";s:14:"admin@fake.com";s:8:"password";s:65:"dda41476e1c43ddd832e1b9afc3d85ea:UXOJIAMTh3meI7iICS6DoCWCe9QOj3zL";s:14:"password_clear";s:0:"";s:8:"usertype";s:10:"deprecated";s:5:"block";s:1:"0";s:9:"sendEmail";s:1:"1";s:12:"registerDate";s:19:"2012-01-17 06:21:22";s:13:"lastvisitDate";s:19:"1900-01-01T00:00:00.000";s:10:"activation";s:1:"0";s:6:"params";s:0:"";s:6:"groups";a:1:{i:8;s:1:"8";}s:5:"guest";i:0;s:10:"\0*\0_params";O:9:"JRegistry":1:{s:7:"\0*\0data";O:8:"stdClass":0:{}}s:14:"\0*\0_authGroups";a:2:{i:0;i:1;i:1;i:8;}s:14:"\0*\0_authLevels";a:4:{i:0;i:1;i:1;i:1;i:2;i:2;i:3;i:3;}s:15:"\0*\0_authActions";N;s:12:"\0*\0_errorMsg";N;s:10:"\0*\0_errors";a:0:{}s:3:"aid";i:0;}s:13:"session.token";s:32:"f0e71b2dff6e77342ec7b405d418282b";}', 42, 'admin', '');

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__template_styles]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__template_styles](
	[id] [bigint] IDENTITY(7,1) NOT NULL,
	[template] [nvarchar](50) NOT NULL,
	[client_id] [tinyint] NOT NULL,
	[home] [nchar](7) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__template_styles_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__template_styles]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_home] ON [#__template_styles] 
(
	[home] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__template_styles]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_template] ON [#__template_styles] 
(
	[template] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__t__templ__0D44F85C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__t__templ__0D44F85C]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__t__templ__0D44F85C]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT (N'') FOR [template]
END


End;

/****** Object:  Default [DF__#__t__clien__0E391C95]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__t__clien__0E391C95]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__t__clien__0E391C95]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

/****** Object:  Default [DF__#__te__home__0F2D40CE]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__te__home__0F2D40CE]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__te__home__0F2D40CE]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT (N'0') FOR [home]
END


End;

/****** Object:  Default [DF__#__t__title__10216507]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__t__title__10216507]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__t__title__10216507]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT (N'') FOR [title]
END


End;

SET IDENTITY_INSERT [#__template_styles] ON;
INSERT INTO[#__template_styles] ([id], [template], [client_id], [home], [title], [params]) VALUES (2, N'bluestork', 1, N'1      ', N'Bluestork - Default', N'{"useRoundedCorners":"1","showSiteName":"0"}');
INSERT INTO[#__template_styles] ([id], [template], [client_id], [home], [title], [params]) VALUES (3, N'atomic', 0, N'0      ', N'Atomic - Default', N'{"navcolor":"#362620"}');
INSERT INTO[#__template_styles] ([id], [template], [client_id], [home], [title], [params]) VALUES (4, N'beez_20', 0, N'1      ', N'Beez2 - Default', N'{"wrapperSmall":53,"wrapperLarge":72,"logo":"","sitetitle":"Your Site Title","sitedescription":"A tag line or description","navposition":"center","templatecolor":"nature"}');
INSERT INTO[#__template_styles] ([id], [template], [client_id], [home], [title], [params]) VALUES (5, N'hathor', 1, N'0      ', N'Hathor - Default', N'{"showSiteName":"0","colourChoice":"","boldText":"0"}');
INSERT INTO[#__template_styles] ([id], [template], [client_id], [home], [title], [params]) VALUES (6, N'beez5', 0, N'0      ', N'Beez5 - Default', N'{"wrapperSmall":53,"wrapperLarge":72,"logo":"images\/sampledata\/fruitshop\/fruits.gif","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","html5":0}');
SET IDENTITY_INSERT [#__template_styles] OFF;

/****** Object:  Table [#__updates]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__updates]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__updates](
	[update_id] [int] IDENTITY(1,1) NOT NULL,
	[update_site_id] [int] NULL,
	[extension_id] [int] NULL,
	[cateryid] [int] NULL,
	[name] [nvarchar](100) NULL,
	[description] [nvarchar](max) NOT NULL,
	[element] [nvarchar](100) NULL,
	[type] [nvarchar](20) NULL,
	[folder] [nvarchar](20) NULL,
	[client_id] [smallint] NULL,
	[version] [nvarchar](10) NULL,
	[data] [nvarchar](max) NOT NULL,
	[detailsurl] [nvarchar](max) NOT NULL,
	[infourl] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__updates_update_id] PRIMARY KEY CLUSTERED 
(
	[update_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__u__updat__1D7B6025]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__updat__1D7B6025]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__updat__1D7B6025]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [update_site_id]
END


End;

/****** Object:  Default [DF__#__u__exten__1E6F845E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__exten__1E6F845E]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__exten__1E6F845E]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [extension_id]
END


End;

/****** Object:  Default [DF__#__u__categ__1F63A897]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__categ__1F63A897]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__categ__1F63A897]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [cateryid]
END


End;

/****** Object:  Default [DF__#__up__name__2057CCD0]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__up__name__2057CCD0]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__up__name__2057CCD0]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__u__eleme__214BF109]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__eleme__214BF109]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__eleme__214BF109]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [element]
END


End;

/****** Object:  Default [DF__#__up__type__22401542]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__up__type__22401542]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__up__type__22401542]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [type]
END


End;

/****** Object:  Default [DF__#__u__folde__2334397B]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__folde__2334397B]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__folde__2334397B]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [folder]
END


End;

/****** Object:  Default [DF__#__u__clien__24285DB4]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__clien__24285DB4]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__clien__24285DB4]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

/****** Object:  Default [DF__#__u__versi__251C81ED]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__versi__251C81ED]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__versi__251C81ED]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [version]
END


End;

/****** Object:  Table [#__update_sites]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__update_sites]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__update_sites](
	[update_site_id] [int] IDENTITY(3,1) NOT NULL,
	[name] [nvarchar](100) NULL,
	[type] [nvarchar](20) NULL,
	[location] [nvarchar](max) NOT NULL,
	[enabled] [int] NULL,
	[last_check_timestamp] [bigint] NULL,
 CONSTRAINT [PK_#__update_sites_update_site_id] PRIMARY KEY CLUSTERED 
(
	[update_site_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__up__name__15DA3E5D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__up__name__15DA3E5D]') AND parent_object_id = OBJECT_ID(N'[#__update_sites]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__up__name__15DA3E5D]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__up__type__16CE6296]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__up__type__16CE6296]') AND parent_object_id = OBJECT_ID(N'[#__update_sites]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__up__type__16CE6296]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites] ADD  DEFAULT (N'') FOR [type]
END


End;

/****** Object:  Default [DF__#__u__enabl__17C286CF]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__enabl__17C286CF]') AND parent_object_id = OBJECT_ID(N'[#__update_sites]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__enabl__17C286CF]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites] ADD  DEFAULT ((0)) FOR [enabled]
END


End;

/****** Object:  Default [DF__#__u__last___18B6AB08]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__last___18B6AB08]') AND parent_object_id = OBJECT_ID(N'[#__update_sites]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__last___18B6AB08]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites] ADD  DEFAULT ((0)) FOR [last_check_timestamp]
END


End;

SET IDENTITY_INSERT [#__update_sites] ON
INSERT INTO[#__update_sites] ([update_site_id], [name], [type], [location], [enabled], [last_check_timestamp]) VALUES (1, 'Joomla Core', 'collection', 'http://update.joomla.org/core/list.xml', 1, 1326803144);
INSERT INTO[#__update_sites] ([update_site_id], [name], [type], [location], [enabled], [last_check_timestamp]) VALUES (2, 'Joomla Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', 1, 1326803144);
SET IDENTITY_INSERT [#__update_sites] OFF



/****** Object:  Table [#__update_categories]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__banners]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__update_categories](
	[cateryid] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](20) NULL,
	[description] [nvarchar](max) NOT NULL,
	[parent] [int] NULL,
	[updatesite] [int] NULL,
 CONSTRAINT [PK_#__update_cateries_cateryid] PRIMARY KEY CLUSTERED 
(
	[cateryid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__up__name__1209AD79]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__up__name__1209AD79]') AND parent_object_id = OBJECT_ID(N'[#__update_categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__up__name__1209AD79]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_categories] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__u__paren__12FDD1B2]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__paren__12FDD1B2]') AND parent_object_id = OBJECT_ID(N'[#__update_categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__paren__12FDD1B2]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_categories] ADD  DEFAULT ((0)) FOR [parent]
END


End;

/****** Object:  Default [DF__#__u__updat__13F1F5EB]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__updat__13F1F5EB]') AND parent_object_id = OBJECT_ID(N'[#__update_categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__updat__13F1F5EB]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_categories] ADD  DEFAULT ((0)) FOR [updatesite]
END


End;

/****** Object:  Table [#__update_sites_extensions]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__update_sites_extensions]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__update_sites_extensions](
	[update_site_id] [int] NOT NULL,
	[extension_id] [int] NOT NULL,
 CONSTRAINT [PK_#__update_sites_extensions_update_site_id] PRIMARY KEY CLUSTERED 
(
	[update_site_id] ASC,
	[extension_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__u__updat__1A9EF37A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__update_sites_extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites_extensions] ADD  DEFAULT ((0)) FOR [update_site_id]
END


End;

/****** Object:  Default [DF__#__u__exten__1B9317B3]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__update_sites_extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__ass__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites_extensions] ADD  DEFAULT ((0)) FOR [extension_id]
END


End;

INSERT INTO[#__update_sites_extensions] ([update_site_id], [extension_id]) VALUES (1, 700);
INSERT INTO[#__update_sites_extensions] ([update_site_id], [extension_id]) VALUES (2, 700);

/****** Object:  Table [#__usergroups]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__usergroups](
	[id] [bigint] IDENTITY(10,1) NOT NULL,
	[parent_id] [bigint] NOT NULL,
	[lft] [int] NOT NULL,
	[rgt] [int] NOT NULL,
	[title] [nvarchar](100) NOT NULL,
 CONSTRAINT [PK_#__usergroups_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_usergroup_adjacency_lookup] ON [#__usergroups] 
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_usergroup_nested_set_lookup] ON [#__usergroups] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_usergroup_title_lookup] ON [#__usergroups] 
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__u__paren__382F5661]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__paren__382F5661]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__paren__382F5661]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT ((0)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__use__lft__39237A9A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__use__lft__39237A9A]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__use__lft__39237A9A]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__use__rgt__3A179ED3]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__use__rgt__3A179ED3]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__use__rgt__3A179ED3]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT ((0)) FOR [rgt]
END


End;

/****** Object:  Default [DF__#__u__title__3B0BC30C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__title__3B0BC30C]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__title__3B0BC30C]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT (N'') FOR [title]
END


End;

SET IDENTITY_INSERT [#__usergroups] ON;
INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (1, 0, 1, 18, N'Public');
INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (2, 1, 8, 15, N'Registered');
INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (3, 2, 9, 14, N'Author');
INSERT INTO[#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (4, 3, 10, 13, N'Editor');
INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (5, 4, 11, 12, N'Publisher');
INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (6, 1, 4, 7, N'Manager');
INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (7, 6, 5, 6, N'Administrator');
INSERT INTO [#__usergroups] ([id], [parent_id], [lft], [rgt], [title]) VALUES (8, 1, 16, 17, N'Super Users');
SET IDENTITY_INSERT [#__usergroups] OFF;

/****** Object:  Table [#__users]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__users]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__users](
	[id] [int] IDENTITY(43,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[username] [nvarchar](150) NOT NULL,
	[email] [nvarchar](100) NOT NULL,
	[password] [nvarchar](100) NOT NULL,
	[usertype] [nvarchar](25) NOT NULL,
	[block] [smallint] NOT NULL,
	[sendEmail] [smallint] NULL,
	[registerDate] [datetime2](0) NOT NULL,
	[lastvisitDate] [datetime2](0) NOT NULL,
	[activation] [nvarchar](100) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__users_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [email] ON [#__users] 
(
	[email] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_block] ON [#__users] 
(
	[block] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_name] ON [#__users] 
(
	[name] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [username] ON [#__users] 
(
	[username] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [usertype] ON [#__users] 
(
	[usertype] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__us__name__3CF40B7E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__us__name__3CF40B7E]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__us__name__3CF40B7E]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__u__usern__3DE82FB7]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__usern__3DE82FB7]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__usern__3DE82FB7]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [username]
END


End;

/****** Object:  Default [DF__#__u__email__3EDC53F0]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__email__3EDC53F0]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__email__3EDC53F0]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [email]
END


End;

/****** Object:  Default [DF__#__u__passw__3FD07829]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__passw__3FD07829]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__passw__3FD07829]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [password]
END


End;

/****** Object:  Default [DF__#__u__usert__40C49C62]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__usert__40C49C62]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__usert__40C49C62]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [usertype]
END


End;

/****** Object:  Default [DF__#__u__block__41B8C09B]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__block__41B8C09B]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__block__41B8C09B]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT ((0)) FOR [block]
END


End;

/****** Object:  Default [DF__#__u__sendE__42ACE4D4]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__sendE__42ACE4D4]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__sendE__42ACE4D4]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT ((0)) FOR [sendEmail]
END


End;

/****** Object:  Default [DF__#__u__regis__43A1090D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__regis__43A1090D]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__regis__43A1090D]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT (getdate()) FOR [registerDate]
END


End;

/****** Object:  Default [DF__#__u__lastv__44952D46]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__lastv__44952D46]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__lastv__44952D46]') AND type = 'D')
BEGIN

ALTER TABLE [#__users] ADD  DEFAULT (getdate()) FOR [lastvisitDate]
END


End;

/****** Object:  Default [DF__#__u__activ__4589517F]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__activ__4589517F]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__activ__4589517F]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [activation]
END


End;

SET IDENTITY_INSERT [#__users] ON;
INSERT INTO[#__users] ([id], [name], [username], [email], [password], [usertype], [block], [sendEmail], [registerDate], [lastvisitDate], [activation], [params]) VALUES (42, N'Super User', N'admin', N'admin@fake.com', N'1464a44a47c12cd4bba35ec6e616dbcd:2WI3SPkGxbttLeEEbhkS1qWVL8Qbhwig', N'deprecated', 0, 1, CAST(0x00A7CE0036350B0000 AS DateTime2), CAST(0x001AD20036350B0000 AS DateTime2), N'0', N'');
SET IDENTITY_INSERT [#__users] OFF;

/****** Object:  Table [#__user_notes]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__user_notes]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__user_notes](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [bigint] NOT NULL,
	[catid] [bigint] NOT NULL,
	[subject] [nvarchar](100) NOT NULL,
	[body] [nvarchar](max) NOT NULL,
	[state] [smallint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[created_user_id] [bigint] NOT NULL,
	[created_time] [datetime2](0) NOT NULL,
	[modified_user_id] [bigint] NOT NULL,
	[modified_time] [datetime2](0) NOT NULL,
	[review_time] [datetime2](0) NOT NULL,
	[publish_up] [datetime2](0) NOT NULL,
	[publish_down] [datetime2](0) NOT NULL,
 CONSTRAINT [PK_#__user_notes_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__user_notes]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_catery_id] ON [#__user_notes] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__user_notes]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_user_id] ON [#__user_notes] 
(
	[user_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__u__user___2704CA5F]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__user___2704CA5F]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__user___2704CA5F]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [user_id]
END


End;

/****** Object:  Default [DF__#__u__catid__27F8EE98]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__catid__27F8EE98]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__catid__27F8EE98]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__u__subje__28ED12D1]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__subje__28ED12D1]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__subje__28ED12D1]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (N'') FOR [subject]
END


End;

/****** Object:  Default [DF__#__u__state__29E1370A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__state__29E1370A]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__state__29E1370A]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__u__check__2AD55B43]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__check__2AD55B43]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__check__2AD55B43]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__u__check__2BC97F7C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__check__2BC97F7C]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__check__2BC97F7C]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__u__creat__2CBDA3B5]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__creat__2CBDA3B5]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__creat__2CBDA3B5]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [created_user_id]
END


End;

/****** Object:  Default [DF__#__u__creat__2DB1C7EE]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__creat__2DB1C7EE]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__creat__2DB1C7EE]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [created_time]
END


End;

/****** Object:  Default [DF__#__u__modif__2EA5EC27]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__modif__2EA5EC27]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__modif__2EA5EC27]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [modified_time]
END


End;

/****** Object:  Default [DF__#__u__revie__2F9A1060]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__revie__2F9A1060]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__revie__2F9A1060]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [review_time]
END


End;

/****** Object:  Default [DF__#__u__publi__308E3499]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__publi__308E3499]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__publi__308E3499]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__u__publi__318258D2]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__publi__318258D2]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__publi__318258D2]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [publish_down]
END


End;

/****** Object:  Table [#__user_profiles]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__user_profiles]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__user_profiles](
	[user_id] [int] NOT NULL,
	[profile_key] [nvarchar](100) NOT NULL,
	[profile_value] [nvarchar](255) NOT NULL,
	[ordering] [int] NOT NULL
)
END;

/****** Object:  Default [DF__#__u__order__336AA144]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__order__336AA144]') AND parent_object_id = OBJECT_ID(N'[#__user_profiles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__order__336AA144]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_profiles] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__user_usergroup_map]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__user_usergroup_map](
	[user_id] [bigint] NOT NULL,
	[group_id] [bigint] NOT NULL,
 CONSTRAINT [PK_#__user_usergroup_map_user_id] PRIMARY KEY CLUSTERED 
(
	[user_id] ASC,
	[group_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__u__user___3552E9B6]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__user___3552E9B6]') AND parent_object_id = OBJECT_ID(N'[#__user_profiles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__user___3552E9B6]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_usergroup_map] ADD  DEFAULT ((0)) FOR [user_id]
END


End;

/****** Object:  Default [DF__#__u__group__36470DEF]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__u__group__36470DEF]') AND parent_object_id = OBJECT_ID(N'[#__user_profiles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__u__group__36470DEF]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_usergroup_map] ADD  DEFAULT ((0)) FOR [group_id]
END


End;


INSERT INTO[#__user_usergroup_map] ([user_id], [group_id]) VALUES (42, 8);

/****** Object:  Table [#__viewlevels]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__viewlevels]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__viewlevels](
	[id] [bigint] IDENTITY(5,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[ordering] [int] NOT NULL,
	[rules] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__viewlevels_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__v__title__477199F1]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__v__title__477199F1]') AND parent_object_id = OBJECT_ID(N'[#__viewlevels]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__v__title__477199F1]') AND type = 'D')
BEGIN
ALTER TABLE [#__viewlevels] ADD  DEFAULT (N'') FOR [title]
END


End;

/****** Object:  Default [DF__#__v__order__4865BE2A]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__v__order__4865BE2A]') AND parent_object_id = OBJECT_ID(N'[#__viewlevels]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__v__order__4865BE2A]') AND type = 'D')
BEGIN
ALTER TABLE [#__viewlevels] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

SET IDENTITY_INSERT [#__viewlevels] ON;
INSERT INTO[#__viewlevels] ([id], [title], [ordering], [rules]) VALUES (1, N'Public', 0, N'[1]');
INSERT INTO [#__viewlevels] ([id], [title], [ordering], [rules]) VALUES (2, N'Registered', 1, N'[6,2,8]');
INSERT INTO[#__viewlevels] ([id], [title], [ordering], [rules]) VALUES (3, N'Special', 2, N'[6,3,8]');
SET IDENTITY_INSERT [#__viewlevels] OFF;

/****** Object:  Table [#__weblinks]    Script Date: 03/16/2012 11:47:40 ******/


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__weblinks](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[catid] [int] NOT NULL,
	[sid] [int] NOT NULL,
	[title] [nvarchar](250) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[url] [nvarchar](250) NOT NULL,
	[description] [nvarchar](max) NOT NULL,
	[date] [datetime2](0) NOT NULL,
	[hits] [int] NOT NULL,
	[state] [smallint] NOT NULL,
	[checked_out] [int] NOT NULL,
	[checked_out_time] [datetime2](0) NOT NULL,
	[ordering] [int] NOT NULL,
	[archived] [smallint] NOT NULL,
	[approved] [smallint] NOT NULL,
	[access] [int] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[language] [nchar](7) NOT NULL,
	[created] [datetime2](0) NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime2](0) NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[featured] [tinyint] NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime2](0) NOT NULL,
	[publish_down] [datetime2](0) NOT NULL,
 CONSTRAINT [PK_#__weblinks_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_access] ON [#__weblinks] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__weblinks] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__weblinks] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__weblinks] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__weblinks] 
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__weblinks] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__weblinks] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__weblinks] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

/****** Object:  Default [DF__#__w__catid__4A4E069C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__catid__4A4E069C]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__catid__4A4E069C]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__web__sid__4B422AD5]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__web__sid__4B422AD5]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__web__sid__4B422AD5]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [sid]
END


End;

/****** Object:  Default [DF__#__w__title__4C364F0E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__title__4C364F0E]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__title__4C364F0E]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [title]
END


End;

/****** Object:  Default [DF__#__w__alias__4D2A7347]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__alias__4D2A7347]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__alias__4D2A7347]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__web__url__4E1E9780]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__web__url__4E1E9780]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__web__url__4E1E9780]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [url]
END


End;

/****** Object:  Default [DF__#__we__date__4F12BBB9]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__we__date__4F12BBB9]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__we__date__4F12BBB9]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (getdate()) FOR [date]
END


End;

/****** Object:  Default [DF__#__we__hits__5006DFF2]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__we__hits__5006DFF2]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__we__hits__5006DFF2]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [hits]
END


End;

/****** Object:  Default [DF__#__w__state__50FB042B]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__state__50FB042B]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__state__50FB042B]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__w__check__51EF2864]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__check__51EF2864]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__check__51EF2864]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__w__check__52E34C9D]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__check__52E34C9D]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__check__52E34C9D]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__w__order__53D770D6]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__order__53D770D6]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__order__53D770D6]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__w__archi__54CB950F]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__archi__54CB950F]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__archi__54CB950F]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [archived]
END


End;

/****** Object:  Default [DF__#__w__appro__55BFB948]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__appro__55BFB948]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__appro__55BFB948]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((1)) FOR [approved]
END


End;

/****** Object:  Default [DF__#__w__acces__56B3DD81]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__acces__56B3DD81]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__acces__56B3DD81]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((1)) FOR [access]
END


End;
/****** Object:  Default [DF__#__w__langu__57A801BA]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__langu__57A801BA]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__langu__57A801BA]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Default [DF__#__w__creat__589C25F3]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__creat__589C25F3]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__creat__589C25F3]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (getdate()) FOR [created]
END


End;

/****** Object:  Default [DF__#__w__creat__59904A2C]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__creat__59904A2C]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__creat__59904A2C]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [created_by]
END


End;

/****** Object:  Default [DF__#__w__creat__5A846E65]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__creat__5A846E65]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__creat__5A846E65]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [created_by_alias]
END


End;

/****** Object:  Default [DF__#__w__modif__5B78929E]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__modif__5B78929E]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__modif__5B78929E]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (getdate()) FOR [modified]
END


End;

/****** Object:  Default [DF__#__w__modif__5C6CB6D7]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__modif__5C6CB6D7]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__modif__5C6CB6D7]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__w__featu__5D60DB10]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__featu__5D60DB10]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__featu__5D60DB10]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [featured]
END


End;

/****** Object:  Default [DF__#__w__publi__5E54FF49]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__publi__5E54FF49]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__publi__5E54FF49]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (getdate()) FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__w__publi__5F492382]    Script Date: 03/16/2012 11:47:40 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__w__publi__5F492382]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__w__publi__5F492382]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (getdate()) FOR [publish_down]
END


End;

SET IDENTITY_INSERT [#__weblinks] ON;

INSERT INTO [#__weblinks] ( [id], [catid], [sid], [title], [alias], [url], [description], [date], [hits], [state], [checked_out], [checked_out_time], [ordering], [archived], [approved], [access], [params], [language], [created], [created_by], [created_by_alias], [modified], [modified_by], [metakey], [metadesc], [metadata], [featured], [xreference], [publish_up], [publish_down]) VALUES
(1, 8, 0, 'Joomla! Community', 'joomla-community', 'http://community.joomla.org/blogs/community.html', '', '1900-01-01T00:00:00.000', 0, 1, 0, '1900-01-01T00:00:00.000', 1, 0, 1, 1, '{"target":"","width":"","height":"","count_clicks":""}', '*', '2012-01-04 15:04:03', 42, '', '2012-01-04 16:17:27', 42, '', '', '', 0, '', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000');
INSERT INTO [#__weblinks] ( [id], [catid], [sid], [title], [alias], [url], [description], [date], [hits], [state], [checked_out], [checked_out_time], [ordering], [archived], [approved], [access], [params], [language], [created], [created_by], [created_by_alias], [modified], [modified_by], [metakey], [metadesc], [metadata], [featured], [xreference], [publish_up], [publish_down]) VALUES(2, 8, 0, 'Joomla! Leadership Blog', 'joomla-leadership-blog', 'http://community.joomla.org/blogs/leadership.html', '', '1900-01-01T00:00:00.000', 0, 1, 0, '1900-01-01T00:00:00.000', 2, 0, 1, 1, '{"target":"","width":"","height":"","count_clicks":""}', '*', '2012-01-04 15:04:48', 42, '', '2012-01-04 16:17:27', 42, '', '', '', 0, '', '1900-01-01T00:00:00.000', '1900-01-01T00:00:00.000');

SET IDENTITY_INSERT [#__weblinks] OFF;