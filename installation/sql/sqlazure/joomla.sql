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
	[date] [datetime] NOT NULL,
	[hits] [int] NOT NULL,
	[state] [smallint] NOT NULL,
	[checked_out] [int] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
	[ordering] [int] NOT NULL,
	[archived] [smallint] NOT NULL,
	[approved] [smallint] NOT NULL,
	[access] [int] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[language] [nvarchar](7) NOT NULL,
	[created] [datetime] NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime] NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[featured] [tinyint] NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime] NOT NULL,
	[publish_down] [datetime] NOT NULL,
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
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_catid')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__weblinks] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_checkout')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__weblinks] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_createdby')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__weblinks] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_featured_catid')
CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__weblinks] 
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__weblinks] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_state')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__weblinks] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__weblinks]') AND name = N'idx_xreference')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__weblinks] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__catid__04AFB25B]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__catid__04AFB25B]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [catid]
END


End;


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__weblink__sid__05A3D694]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__weblink__sid__05A3D694]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [sid]
END


End;


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__title__0697FACD]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__title__0697FACD]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [title]
END


End;


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__alias__078C1F06]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__alias__078C1F06]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__weblink__url__0880433F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__weblink__url__0880433F]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__weblink__url__0880433F]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [url]
END


End;

/****** Object:  Default [DF__#__weblin__date__09746778]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__weblin__date__09746778]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__weblin__date__09746778]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [date]
END


End;

/****** Object:  Default [DF__#__weblin__hits__0A688BB1]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__weblin__hits__0A688BB1]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__weblin__hits__0A688BB1]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [hits]
END


End;

/****** Object:  Default [DF__#__webli__state__0B5CAFEA]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__state__0B5CAFEA]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__state__0B5CAFEA]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__webli__check__0C50D423]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__check__0C50D423]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__check__0C50D423]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__webli__check__0D44F85C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__check__0D44F85C]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__check__0D44F85C]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__webli__order__0E391C95]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__order__0E391C95]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__order__0E391C95]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__webli__archi__0F2D40CE]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__archi__0F2D40CE]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__archi__0F2D40CE]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [archived]
END


End;

/****** Object:  Default [DF__#__webli__appro__10216507]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__appro__10216507]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__appro__10216507]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((1)) FOR [approved]
END


End;

/****** Object:  Default [DF__#__webli__acces__11158940]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__acces__11158940]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__acces__11158940]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((1)) FOR [access]
END


End;

/****** Object:  Default [DF__#__webli__langu__1209AD79]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__langu__1209AD79]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__langu__1209AD79]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Default [DF__#__webli__creat__12FDD1B2]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__creat__12FDD1B2]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__creat__12FDD1B2]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created]
END


End;

/****** Object:  Default [DF__#__webli__creat__13F1F5EB]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__creat__13F1F5EB]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__creat__13F1F5EB]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [created_by]
END


End;

/****** Object:  Default [DF__#__webli__creat__14E61A24]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__creat__14E61A24]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__creat__14E61A24]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT (N'') FOR [created_by_alias]
END


End;

/****** Object:  Default [DF__#__webli__modif__15DA3E5D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__modif__15DA3E5D]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__modif__15DA3E5D]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [modified]
END


End;

/****** Object:  Default [DF__#__webli__modif__16CE6296]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__modif__16CE6296]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__modif__16CE6296]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__webli__featu__17C286CF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__featu__17C286CF]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__featu__17C286CF]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ((0)) FOR [featured]
END


End;

/****** Object:  Default [DF__#__webli__publi__18B6AB08]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__publi__18B6AB08]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__publi__18B6AB08]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__webli__publi__19AACF41]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__webli__publi__19AACF41]') AND parent_object_id = OBJECT_ID(N'[#__weblinks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__webli__publi__19AACF41]') AND type = 'D')
BEGIN
ALTER TABLE [#__weblinks] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_down]
END


End;


/****** Object:  Table [#__viewlevels]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__viewlevels]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__viewlevels](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[ordering] [int] NOT NULL,
	[rules] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__viewlevels_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__viewlevels$idx_assetgroup_title_lookup] UNIQUE NONCLUSTERED 
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;


SET IDENTITY_INSERT #__viewlevels  ON;
INSERT INTO #__viewlevels (id, title, ordering, rules) 
SELECT 1, 'Public', 0, '[1]'
UNION ALL
SELECT 2, 'Registered', 1, '[6,2,8]'
UNION ALL
SELECT 3, 'Special', 2, '[6,3,8]';

SET IDENTITY_INSERT #__viewlevels  OFF;
/****** Object:  Default [DF__#__viewl__title__01D345B0]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__viewl__title__01D345B0]') AND parent_object_id = OBJECT_ID(N'[#__viewlevels]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__viewl__title__01D345B0]') AND type = 'D')
BEGIN
ALTER TABLE [#__viewlevels] ADD  DEFAULT (N'') FOR [title]
END


End;

/****** Object:  Default [DF__#__viewl__order__02C769E9]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__viewl__order__02C769E9]') AND parent_object_id = OBJECT_ID(N'[#__viewlevels]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__viewl__order__02C769E9]') AND type = 'D')
BEGIN
ALTER TABLE [#__viewlevels] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Table [#__users]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__users]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__users](
	[id] [int] IDENTITY(42,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[username] [nvarchar](150) NOT NULL,
	[email] [nvarchar](100) NOT NULL,
	[password] [nvarchar](100) NOT NULL,
	[usertype] [nvarchar](25) NOT NULL,
	[block] [smallint] NOT NULL,
	[sendEmail] [smallint] NULL,
	[registerDate] [datetime] NOT NULL,
	[lastvisitDate] [datetime] NOT NULL,
	[activation] [nvarchar](100) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__users_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'email')
CREATE NONCLUSTERED INDEX [email] ON [#__users] 
(
	[email] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'idx_block')
CREATE NONCLUSTERED INDEX [idx_block] ON [#__users] 
(
	[block] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'idx_name')
CREATE NONCLUSTERED INDEX [idx_name] ON [#__users] 
(
	[name] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'username')
CREATE NONCLUSTERED INDEX [username] ON [#__users] 
(
	[username] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__users]') AND name = N'usertype')
CREATE NONCLUSTERED INDEX [usertype] ON [#__users] 
(
	[usertype] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__users__name__7755B73D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__name__7755B73D]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__name__7755B73D]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__users__usern__7849DB76]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__usern__7849DB76]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__usern__7849DB76]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [username]
END


End;

/****** Object:  Default [DF__#__users__email__793DFFAF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__email__793DFFAF]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__email__793DFFAF]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [email]
END


End;

/****** Object:  Default [DF__#__users__passw__7A3223E8]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__passw__7A3223E8]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__passw__7A3223E8]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [password]
END


End;

/****** Object:  Default [DF__#__users__usert__7B264821]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__usert__7B264821]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__usert__7B264821]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [usertype]
END


End;

/****** Object:  Default [DF__#__users__block__7C1A6C5A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__block__7C1A6C5A]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__block__7C1A6C5A]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT ((0)) FOR [block]
END


End;

/****** Object:  Default [DF__#__users__sendE__7D0E9093]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__sendE__7D0E9093]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__sendE__7D0E9093]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT ((0)) FOR [sendEmail]
END


End;

/****** Object:  Default [DF__#__users__regis__7E02B4CC]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__regis__7E02B4CC]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__regis__7E02B4CC]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [registerDate]
END


End;

/****** Object:  Default [DF__#__users__lastv__7EF6D905]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__lastv__7EF6D905]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__lastv__7EF6D905]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [lastvisitDate]
END


End;

/****** Object:  Default [DF__#__users__activ__7FEAFD3E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__users__activ__7FEAFD3E]') AND parent_object_id = OBJECT_ID(N'[#__users]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__users__activ__7FEAFD3E]') AND type = 'D')
BEGIN
ALTER TABLE [#__users] ADD  DEFAULT (N'') FOR [activation]
END


End;


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
/****** Object:  Table [#__user_usergroup_map]    Script Date: 11/08/2010 18:41:22 ******/


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

/****** Object:  Default [DF__#__user___user___6FB49575]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___user___6FB49575]') AND parent_object_id = OBJECT_ID(N'[#__user_usergroup_map]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___user___6FB49575]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_usergroup_map] ADD  DEFAULT ((0)) FOR [user_id]
END


End;


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___group__70A8B9AE]') AND parent_object_id = OBJECT_ID(N'[#__user_usergroup_map]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___group__70A8B9AE]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_usergroup_map] ADD  DEFAULT ((0)) FOR [group_id]
END


End;


/****** Object:  Table [#__updates]    Script Date: 11/08/2010 18:41:22 ******/


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
 CONSTRAINT [PK_#__updates_update_id] PRIMARY KEY CLUSTERED 
(
	[update_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__updat__updat__6442E2C9]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__updat__6442E2C9]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__updat__6442E2C9]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [update_site_id]
END


End;

/****** Object:  Default [DF__#__updat__exten__65370702]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__exten__65370702]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__exten__65370702]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [extension_id]
END


End;

/****** Object:  Default [DF__#__updat__categ__662B2B3B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__categ__662B2B3B]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__categ__662B2B3B]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [cateryid]
END


End;

/****** Object:  Default [DF__#__update__name__671F4F74]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__update__name__671F4F74]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__update__name__671F4F74]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__updat__eleme__681373AD]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__eleme__681373AD]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__eleme__681373AD]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [element]
END


End;

/****** Object:  Default [DF__#__update__type__690797E6]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__update__type__690797E6]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__update__type__690797E6]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [type]
END


End;

/****** Object:  Default [DF__#__updat__folde__69FBBC1F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__folde__69FBBC1F]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__folde__69FBBC1F]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [folder]
END


End;

/****** Object:  Default [DF__#__updat__clien__6AEFE058]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__clien__6AEFE058]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__clien__6AEFE058]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

/****** Object:  Default [DF__#__updat__versi__6BE40491]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__versi__6BE40491]') AND parent_object_id = OBJECT_ID(N'[#__updates]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__versi__6BE40491]') AND type = 'D')
BEGIN
ALTER TABLE [#__updates] ADD  DEFAULT (N'') FOR [version]
END


End;


/****** Object:  Table [#__update_sites_extensions]    Script Date: 11/08/2010 18:41:22 ******/


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

/****** Object:  Default [DF__#__updat__updat__6166761E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__updat__6166761E]') AND parent_object_id = OBJECT_ID(N'[#__update_sites_extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__updat__6166761E]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites_extensions] ADD  DEFAULT ((0)) FOR [update_site_id]
END


End;

/****** Object:  Default [DF__#__updat__exten__625A9A57]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__exten__625A9A57]') AND parent_object_id = OBJECT_ID(N'[#__update_sites_extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__exten__625A9A57]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites_extensions] ADD  DEFAULT ((0)) FOR [extension_id]
END


End;

INSERT INTO #__update_sites_extensions (update_site_id, extension_id)
SELECT 1, 700
UNION ALL
SELECT 2, 700;


/****** Object:  Table [#__update_sites]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__update_sites]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__update_sites](
	[update_site_id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NULL,
	[type] [nvarchar](20) NULL,
	[location] [nvarchar](max) NOT NULL,
	[enabled] [int] NULL,
	[last_check_timestamp] [int] DEFAULT '0',
 CONSTRAINT [PK_#__update_sites_update_site_id] PRIMARY KEY CLUSTERED 
(
	[update_site_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__update__name__5D95E53A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__update__name__5D95E53A]') AND parent_object_id = OBJECT_ID(N'[#__update_sites]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__update__name__5D95E53A]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites] ADD  DEFAULT (N'') FOR [name]
END


End;
SET IDENTITY_INSERT #__update_sites ON;

INSERT INTO #__update_sites (update_site_id,name ,type,location,enabled,last_check_timestamp) VALUES (1, 'Joomla Core', 'collection', 'http://update.joomla.org/core/list.xml', 1, 0);
  
  
INSERT INTO #__update_sites (update_site_id ,name ,type,location,enabled,last_check_timestamp) VALUES (2, 'Joomla Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', 1, 0);

SET IDENTITY_INSERT #__update_sites OFF;

/****** Object:  Default [DF__#__update__type__5E8A0973]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__update__type__5E8A0973]') AND parent_object_id = OBJECT_ID(N'[#__update_sites]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__update__type__5E8A0973]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites] ADD  DEFAULT (N'') FOR [type]
END


End;
/****** Object:  Default [DF__#__updat__enabl__5F7E2DAC]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__enabl__5F7E2DAC]') AND parent_object_id = OBJECT_ID(N'[#__update_sites]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__enabl__5F7E2DAC]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_sites] ADD  DEFAULT ((0)) FOR [enabled]
END


End;


/****** Object:  Table [#__update_categories]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__update_categories]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__update_categories](
	[cateryid] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](20) NULL,
	[description] [nvarchar](max) NOT NULL,
	[parent] [int] NULL,
	[updatesite] [int] NULL,
 CONSTRAINT [PK_#__update_categories_cateryid] PRIMARY KEY CLUSTERED 
(
	[cateryid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__update__name__59C55456]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__update__name__59C55456]') AND parent_object_id = OBJECT_ID(N'[#__update_categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__update__name__59C55456]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_categories] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__updat__paren__5AB9788F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__paren__5AB9788F]') AND parent_object_id = OBJECT_ID(N'[#__update_categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__paren__5AB9788F]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_categories] ADD  DEFAULT ((0)) FOR [parent]
END


End;

/****** Object:  Default [DF__#__updat__updat__5BAD9CC8]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__updat__updat__5BAD9CC8]') AND parent_object_id = OBJECT_ID(N'[#__update_categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__updat__updat__5BAD9CC8]') AND type = 'D')
BEGIN
ALTER TABLE [#__update_categories] ADD  DEFAULT ((0)) FOR [updatesite]
END


End;


/****** Object:  Table [#__template_styles]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__template_styles]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__template_styles](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[template] [nvarchar](50) NOT NULL,
	[client_id] [tinyint] NOT NULL,
	[home] [nvarchar](7) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__template_styles_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__template_styles]') AND name = N'idx_home')
CREATE NONCLUSTERED INDEX [idx_home] ON [#__template_styles] 
(
	[home] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__template_styles]') AND name = N'idx_template')
CREATE NONCLUSTERED INDEX [idx_template] ON [#__template_styles] 
(
	[template] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__templ__templ__540C7B00]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__templ__templ__540C7B00]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__templ__templ__540C7B00]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT (N'') FOR [template]
END


End;

/****** Object:  Default [DF__#__templ__clien__55009F39]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__templ__clien__55009F39]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__templ__clien__55009F39]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

/****** Object:  Default [DF__#__templa__home__55F4C372]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__templa__home__55F4C372]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__templa__home__55F4C372]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT (('0')) FOR [home]
END


End;

/****** Object:  Default [DF__#__templ__title__56E8E7AB]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__templ__title__56E8E7AB]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__templ__title__56E8E7AB]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT (N'') FOR [title]
END


End;

/****** Object:  Default [DF__#__templ__param__57DD0BE4]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__templ__param__57DD0BE4]') AND parent_object_id = OBJECT_ID(N'[#__template_styles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__templ__param__57DD0BE4]') AND type = 'D')
BEGIN
ALTER TABLE [#__template_styles] ADD  DEFAULT (N'') FOR [params]
END


End;


SET IDENTITY_INSERT #__template_styles  ON;

INSERT INTO #__template_styles (id, template, client_id, home, title, params) VALUES (2, 'bluestork', '1', '1', 'Bluestork - Default', '{"useRoundedCorners":"1","showSiteName":"0"}');
INSERT INTO #__template_styles (id, template, client_id, home, title, params) VALUES (3, 'atomic', '0', '0', 'Atomic - Default', '{}');
INSERT INTO #__template_styles (id, template, client_id, home, title, params) VALUES (4, 'beez_20', 0, 1, 'Beez2 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\\/joomla_black.gif","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","templatecolor":"personal","html5":"0"}');
INSERT INTO #__template_styles (id, template, client_id, home, title, params) VALUES (5, 'hathor', '1', '0', 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}');
INSERT INTO #__template_styles (id, template, client_id, home, title, params) VALUES (6, 'beez5', 0, 0, 'Beez5 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\\/sampledata\\/fruitshop\\/fruits.gif","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","html5":"0"}');
SET IDENTITY_INSERT #__template_styles  OFF;


/****** Object:  Table [#__session]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__session]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__session](
	[session_id] [nvarchar](32) NOT NULL,
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__session]') AND name = N'time')
CREATE NONCLUSTERED INDEX [time] ON [#__session] 
(
	[time] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__session]') AND name = N'userid')
CREATE NONCLUSTERED INDEX [userid] ON [#__session] 
(
	[userid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__session]') AND name = N'whosonline')
CREATE NONCLUSTERED INDEX [whosonline] ON [#__session] 
(
	[guest] ASC,
	[usertype] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__sessi__sessi__4B7734FF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessi__sessi__4B7734FF]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessi__sessi__4B7734FF]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [session_id]
END


End;

/****** Object:  Default [DF__#__sessi__clien__4C6B5938]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessi__clien__4C6B5938]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessi__clien__4C6B5938]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

/****** Object:  Default [DF__#__sessi__guest__4D5F7D71]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessi__guest__4D5F7D71]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessi__guest__4D5F7D71]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT ((1)) FOR [guest]
END


End;

/****** Object:  Default [DF__#__sessio__time__4E53A1AA]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessio__time__4E53A1AA]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessio__time__4E53A1AA]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [time]
END


End;

/****** Object:  Default [DF__#__sessio__data__4F47C5E3]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessio__data__4F47C5E3]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessio__data__4F47C5E3]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (NULL) FOR [data]
END


End;

/****** Object:  Default [DF__#__sessi__useri__503BEA1C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessi__useri__503BEA1C]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessi__useri__503BEA1C]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT ((0)) FOR [userid]
END


End;

/****** Object:  Default [DF__#__sessi__usern__51300E55]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessi__usern__51300E55]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessi__usern__51300E55]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [username]
END


End;

/****** Object:  Default [DF__#__sessi__usert__5224328E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__sessi__usert__5224328E]') AND parent_object_id = OBJECT_ID(N'[#__session]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__sessi__usert__5224328E]') AND type = 'D')
BEGIN
ALTER TABLE [#__session] ADD  DEFAULT (N'') FOR [usertype]
END


End;


/****** Object:  Table [#__schemas]    Script Date: 11/08/2010 18:41:22 ******/


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


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__overrider]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__overrider] (
  [id] [int] IDENTITY(1,1) NOT NULL,  
  [constant] [nvarchar](max) NOT NULL,
  [string] [nvarchar] NOT NULL,
  [file] [nvarchar](max) NOT NULL,
   CONSTRAINT [PK_#__overrider_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;


/****** Object:  Table [#__redirect_links]    Script Date: 11/08/2010 18:41:22 ******/


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
	[created_date] [datetime] NOT NULL,
	[modified_date] [datetime] NOT NULL,
 CONSTRAINT [PK_#__redirect_links_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__redirect_links$idx_link_old] UNIQUE NONCLUSTERED 
(
	[old_url] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__redirect_links]') AND name = N'idx_link_modifed')
CREATE NONCLUSTERED INDEX [idx_link_modifed] ON [#__redirect_links] 
(
	[modified_date] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__redir__creat__47A6A41B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__redir__creat__47A6A41B]') AND parent_object_id = OBJECT_ID(N'[#__redirect_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__redir__creat__47A6A41B]') AND type = 'D')
BEGIN
ALTER TABLE [#__redirect_links] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created_date]
END


End;

/****** Object:  Default [DF__#__redir__modif__489AC854]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__redir__modif__489AC854]') AND parent_object_id = OBJECT_ID(N'[#__redirect_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__redir__modif__489AC854]') AND type = 'D')
BEGIN
ALTER TABLE [#__redirect_links] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [modified_date]
END


End;



/****** Object:  Table [#__newsfeeds]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__newsfeeds](
	[catid] [int] NOT NULL,
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[alias] [nvarchar](100) NOT NULL,
	[link] [nvarchar](200) NOT NULL,
	[filename] [nvarchar](200) NULL,
	[published] [smallint] NOT NULL,
	[numarticles] [bigint] NOT NULL,
	[cache_time] [bigint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
	[ordering] [int] NOT NULL,
	[rtl] [smallint] NOT NULL,
	[access] [int] NOT NULL,
	[language] [nvarchar](7) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[created] [datetime] NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime] NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime] NOT NULL,
	[publish_down] [datetime] NOT NULL,
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
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_catid')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__newsfeeds] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_checkout')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__newsfeeds] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_createdby')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__newsfeeds] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__newsfeeds] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_state')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__newsfeeds] 
(
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__newsfeeds]') AND name = N'idx_xreference')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__newsfeeds] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__newsf__catid__32AB8735]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__catid__32AB8735]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__catid__32AB8735]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__newsfe__name__339FAB6E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsfe__name__339FAB6E]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsfe__name__339FAB6E]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__newsf__alias__3493CFA7]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__alias__3493CFA7]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__alias__3493CFA7]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__newsfe__link__3587F3E0]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsfe__link__3587F3E0]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsfe__link__3587F3E0]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [link]
END


End;

/****** Object:  Default [DF__#__newsf__filen__367C1819]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__filen__367C1819]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__filen__367C1819]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (NULL) FOR [filename]
END


End;

/****** Object:  Default [DF__#__newsf__publi__37703C52]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__publi__37703C52]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__publi__37703C52]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [published]
END


End;
/****** Object:  Default [DF__#__newsf__numar__3864608B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__numar__3864608B]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__numar__3864608B]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((1)) FOR [numarticles]
END


End;

/****** Object:  Default [DF__#__newsf__cache__395884C4]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__cache__395884C4]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__cache__395884C4]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((3600)) FOR [cache_time]
END


End;

/****** Object:  Default [DF__#__newsf__check__3A4CA8FD]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__check__3A4CA8FD]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__check__3A4CA8FD]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;
/****** Object:  Default [DF__#__newsf__check__3B40CD36]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__check__3B40CD36]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__check__3B40CD36]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__newsf__order__3C34F16F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__order__3C34F16F]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__order__3C34F16F]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__newsfee__rtl__3D2915A8]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsfee__rtl__3D2915A8]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsfee__rtl__3D2915A8]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [rtl]
END


End;

/****** Object:  Default [DF__#__newsf__acces__3E1D39E1]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__acces__3E1D39E1]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__acces__3E1D39E1]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__newsf__langu__3F115E1A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__langu__3F115E1A]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__langu__3F115E1A]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Default [DF__#__newsf__creat__40058253]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__creat__40058253]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__creat__40058253]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created]
END


End;

/****** Object:  Default [DF__#__newsf__creat__40F9A68C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__creat__40F9A68C]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__creat__40F9A68C]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [created_by]
END


End;

/****** Object:  Default [DF__#__newsf__creat__41EDCAC5]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__creat__41EDCAC5]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__creat__41EDCAC5]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT (N'') FOR [created_by_alias]
END


End;

/****** Object:  Default [DF__#__newsf__modif__42E1EEFE]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__modif__42E1EEFE]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__modif__42E1EEFE]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [modified]
END


End;

/****** Object:  Default [DF__#__newsf__modif__43D61337]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__modif__43D61337]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__modif__43D61337]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__newsf__publi__44CA3770]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__publi__44CA3770]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__publi__44CA3770]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__newsf__publi__45BE5BA9]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__newsf__publi__45BE5BA9]') AND parent_object_id = OBJECT_ID(N'[#__newsfeeds]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__newsf__publi__45BE5BA9]') AND type = 'D')
BEGIN
ALTER TABLE [#__newsfeeds] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_down]
END


End;


/****** Object:  Table [#__modules_menu]    Script Date: 11/08/2010 18:41:22 ******/


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


/****** Object:  Default [DF__#__modul__modul__2FCF1A8A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__modul__2FCF1A8A]') AND parent_object_id = OBJECT_ID(N'[#__modules_menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__modul__2FCF1A8A]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules_menu] ADD  DEFAULT ((0)) FOR [moduleid]
END


End;

/****** Object:  Default [DF__#__modul__menui__30C33EC3]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__menui__30C33EC3]') AND parent_object_id = OBJECT_ID(N'[#__modules_menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__menui__30C33EC3]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules_menu] ADD  DEFAULT ((0)) FOR [menuid]
END


End;

INSERT INTO #__modules_menu (moduleid,menuid)
SELECT 1,0
UNION ALL
SELECT 2,0
UNION ALL
SELECT 3,0
UNION ALL
SELECT 4,0
UNION ALL
SELECT 6,0
UNION ALL
SELECT 7,0
UNION ALL
SELECT 8,0
UNION ALL
SELECT 9,0
UNION ALL
SELECT 10,0
UNION ALL
SELECT 12,0
UNION ALL
SELECT 13,0
UNION ALL
SELECT 14,0
UNION ALL
SELECT 15,0
UNION ALL
SELECT 16,0
UNION ALL
SELECT 17,0
UNION ALL
SELECT 79,0
UNION ALL 
SELECT 85,0




SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__modules]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__modules](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[note] [nvarchar](255) NOT NULL,
	[content] [nvarchar](max) NOT NULL,
	[ordering] [int] NOT NULL,
	[position] [nvarchar](50) NULL ,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
	[publish_up] [datetime] NOT NULL,
	[publish_down] [datetime] NOT NULL,
	[published] [smallint] NOT NULL,
	[module] [nvarchar](50) NULL,
	[access] [int] NOT NULL,
	[showtitle] [tinyint] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[client_id] [smallint] NOT NULL,
	[language] [nvarchar](7) NOT NULL,
 CONSTRAINT [PK_#__modules_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__modules]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__modules] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__modules]') AND name = N'newsfeeds')
CREATE NONCLUSTERED INDEX [newsfeeds] ON [#__modules] 
(
	[module] ASC,
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__modules]') AND name = N'published')
CREATE NONCLUSTERED INDEX [published] ON [#__modules] 
(
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__modul__title__2180FB33]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__title__2180FB33]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__title__2180FB33]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (N'') FOR [title]
END


End;

/****** Object:  Default [DF__#__module__note__22751F6C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__module__note__22751F6C]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__module__note__22751F6C]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (N'') FOR [note]
END


End;

/****** Object:  Default [DF__#__modul__order__236943A5]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__order__236943A5]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__order__236943A5]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__modul__posit__245D67DE]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__posit__245D67DE]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__posit__245D67DE]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (NULL) FOR [position]
END


End;

/****** Object:  Default [DF__#__modul__check__25518C17]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__check__25518C17]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__check__25518C17]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__modul__check__2645B050]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__check__2645B050]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__check__2645B050]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__modul__publi__2739D489]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__publi__2739D489]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__publi__2739D489]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__modul__publi__282DF8C2]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__publi__282DF8C2]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__publi__282DF8C2]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_down]
END


End;

/****** Object:  Default [DF__#__modul__publi__29221CFB]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__publi__29221CFB]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__publi__29221CFB]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__modul__modul__2A164134]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__modul__2A164134]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__modul__2A164134]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (NULL) FOR [module]
END


End;

/****** Object:  Default [DF__#__modul__acces__2B0A656D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__acces__2B0A656D]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__acces__2B0A656D]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__modul__showt__2BFE89A6]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__showt__2BFE89A6]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__showt__2BFE89A6]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((1)) FOR [showtitle]
END


End;

/****** Object:  Default [DF__#__modul__param__2CF2ADDF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__param__2CF2ADDF]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__param__2CF2ADDF]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT (N'') FOR [params]
END


End;

/****** Object:  Default [DF__#__modul__clien__2DE6D218]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__modul__clien__2DE6D218]') AND parent_object_id = OBJECT_ID(N'[#__modules]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__modul__clien__2DE6D218]') AND type = 'D')
BEGIN
ALTER TABLE [#__modules] ADD  DEFAULT ((0)) FOR [client_id]
END


End;


SET IDENTITY_INSERT #__modules  ON;
INSERT INTO #__modules (id, title, note, content, ordering, position, checked_out,checked_out_time, publish_up, publish_down, published, module, access, showtitle, params,
  client_id, language)
SELECT 1, 'Main Menu', '', '', 1, 'position-7', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_menu', 1, 1, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 2, 'Login', '', '', 1, 'login', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_login', 1, 1, '', 1, '*'
UNION ALL
SELECT 3, 'Popular Articles', '', '', 3, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', 1, '*'
UNION ALL
SELECT 4, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', 1, '*'
UNION ALL

SELECT 8, 'Toolbar', '', '', 1, 'toolbar', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_toolbar', 3, 1, '', 1, '*'
UNION ALL
SELECT 9, 'Quick Icons', '', '', 1, 'icon', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_quickicon', 3, 1, '', 1, '*'
UNION ALL
SELECT 10, 'Logged-in Users', '', '', 2, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', 1, '*'
UNION ALL
SELECT 12, 'Admin Menu', '', '', 1, 'menu', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*'
UNION ALL
SELECT 13, 'Admin Submenu', '', '', 1, 'submenu', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_submenu', 3, 1, '', 1, '*'
UNION ALL
SELECT 14, 'User Status', '', '', 2, 'status', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_status', 3, 1, '', 1, '*'
UNION ALL
SELECT 15, 'Title', '', '', 1, 'title', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_title', 3, 1, '', 1, '*'
UNION ALL
SELECT 16, 'Login Form', '', '', 7, 'position-7', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*'
UNION ALL
SELECT 17, 'Breadcrumbs', '', '', 1, 'position-2', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"Home","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 79, 'Multilanguage status', '', '', 1, 'status', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*';
SET IDENTITY_INSERT #__modules  OFF;


/****** Object:  Table [#__messages_cfg]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__messages_cfg]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__messages_cfg](
	[user_id] [bigint] NOT NULL,
	[cfg_name] [nvarchar](100) NOT NULL,
	[cfg_value] [nvarchar](255) NOT NULL,
 CONSTRAINT [#__messages_cfg$idx_user_var_name] UNIQUE CLUSTERED 
(
	[user_id] ASC,
	[cfg_name] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__messa__user___1DB06A4F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__user___1DB06A4F]') AND parent_object_id = OBJECT_ID(N'[#__messages_cfg]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__user___1DB06A4F]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages_cfg] ADD  DEFAULT ((0)) FOR [user_id]
END


End;

/****** Object:  Default [DF__#__messa__cfg_n__1EA48E88]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__cfg_n__1EA48E88]') AND parent_object_id = OBJECT_ID(N'[#__messages_cfg]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__cfg_n__1EA48E88]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages_cfg] ADD  DEFAULT (N'') FOR [cfg_name]
END


End;

/****** Object:  Default [DF__#__messa__cfg_v__1F98B2C1]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__cfg_v__1F98B2C1]') AND parent_object_id = OBJECT_ID(N'[#__messages_cfg]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__cfg_v__1F98B2C1]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages_cfg] ADD  DEFAULT (N'') FOR [cfg_value]
END


End;



SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__messages]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__messages](
	[message_id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id_from] [bigint] NOT NULL,
	[user_id_to] [bigint] NOT NULL,
	[folder_id] [tinyint] NOT NULL,
	[date_time] [datetime] NOT NULL,
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__messages]') AND name = N'useridto_state')
CREATE NONCLUSTERED INDEX [useridto_state] ON [#__messages] 
(
	[user_id_to] ASC,
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__messa__user___160F4887]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__user___160F4887]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__user___160F4887]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [user_id_from]
END


End;

/****** Object:  Default [DF__#__messa__user___17036CC0]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__user___17036CC0]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__user___17036CC0]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [user_id_to]
END


End;

/****** Object:  Default [DF__#__messa__folde__17F790F9]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__folde__17F790F9]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__folde__17F790F9]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [folder_id]
END


End;

/****** Object:  Default [DF__#__messa__date___18EBB532]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__date___18EBB532]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__date___18EBB532]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [date_time]
END


End;

/****** Object:  Default [DF__#__messa__state__19DFD96B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__state__19DFD96B]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__state__19DFD96B]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__messa__prior__1AD3FDA4]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__prior__1AD3FDA4]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__prior__1AD3FDA4]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT ((0)) FOR [priority]
END


End;

/****** Object:  Default [DF__#__messa__subje__1BC821DD]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__messa__subje__1BC821DD]') AND parent_object_id = OBJECT_ID(N'[#__messages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__messa__subje__1BC821DD]') AND type = 'D')
BEGIN
ALTER TABLE [#__messages] ADD  DEFAULT (N'') FOR [subject]
END


End;



/****** Object:  Table [#__menu_types]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__menu_types]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__menu_types](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[menutype] [nvarchar](24) NOT NULL,
	[title] [nvarchar](48) NOT NULL,
	[description] [nvarchar](255) NOT NULL,
 CONSTRAINT [PK_#__menu_types_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__menu_types$idx_menutype] UNIQUE NONCLUSTERED 
(
	[menutype] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;


/****** Object:  Default [DF__#__menu___descr__14270015]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu___descr__14270015]') AND parent_object_id = OBJECT_ID(N'[#__menu_types]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu___descr__14270015]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu_types] ADD  DEFAULT (N'') FOR [description]
END


End;


/****** Object:  Table [#__menu]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__menu]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__menu](
	[id] [int] IDENTITY(1,1) NOT NULL,
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
	[access] [int] NOT NULL,
	[img] [nvarchar](255) NOT NULL,
	[template_style_id] [bigint] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[lft] [int] NOT NULL,
	[rgt] [int] NOT NULL,
	[home] [tinyint] NOT NULL,
	[language] [nvarchar](7) NOT NULL,
	[client_id] [smallint] NOT NULL,
 CONSTRAINT [PK_#__menu_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__menu$idx_client_id_parent_id_alias] UNIQUE NONCLUSTERED 
(
	[client_id] ASC,
	[parent_id] ASC,
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_alias')
CREATE NONCLUSTERED INDEX [idx_alias] ON [#__menu] 
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_componentid')
CREATE NONCLUSTERED INDEX [idx_componentid] ON [#__menu] 
(
	[component_id] ASC,
	[menutype] ASC,
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__menu] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_left_right')
CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__menu] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_menutype')
CREATE NONCLUSTERED INDEX [idx_menutype] ON [#__menu] 
(
	[menutype] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__menu] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_browserNav')
CREATE NONCLUSTERED INDEX [idx_browserNav] ON [#__menu] 
(
	[browserNav] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_home')
CREATE NONCLUSTERED INDEX [idx_home] ON [#__menu] 
(
	[home] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_template_style_id')
CREATE NONCLUSTERED INDEX [idx_template_style_id] ON [#__menu] 
(
	[template_style_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__menu]') AND name = N'idx_img')
CREATE NONCLUSTERED INDEX [idx_img] ON [#__menu] 
(
	[img] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__menu__note__03F0984C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__note__03F0984C]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__note__03F0984C]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT (N'') FOR [note]
END


End;

/****** Object:  Default [DF__#__menu__publis__04E4BC85]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__publis__04E4BC85]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__publis__04E4BC85]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__menu__parent__05D8E0BE]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__parent__05D8E0BE]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__parent__05D8E0BE]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((1)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__menu__level__06CD04F7]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__level__06CD04F7]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__level__06CD04F7]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [level]
END


End;

/****** Object:  Default [DF__#__menu__compon__07C12930]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__compon__07C12930]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__compon__07C12930]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [component_id]
END


End;

/****** Object:  Default [DF__#__menu__orderi__08B54D69]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__orderi__08B54D69]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__orderi__08B54D69]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__menu__checke__09A971A2]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__checke__09A971A2]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__checke__09A971A2]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__menu__checke__0A9D95DB]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__checke__0A9D95DB]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__checke__0A9D95DB]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__menu__browse__0B91BA14]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__browse__0B91BA14]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__browse__0B91BA14]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [browserNav]
END


End;

/****** Object:  Default [DF__#__menu__access__0C85DE4D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__access__0C85DE4D]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__access__0C85DE4D]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__menu__templa__0D7A0286]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__templa__0D7A0286]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__templa__0D7A0286]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [template_style_id]
END


End;

/****** Object:  Default [DF__#__menu__lft__0E6E26BF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__lft__0E6E26BF]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__lft__0E6E26BF]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__menu__rgt__0F624AF8]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__rgt__0F624AF8]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__rgt__0F624AF8]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [rgt]
END


End;

/****** Object:  Default [DF__#__menu__home__10566F31]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__home__10566F31]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__home__10566F31]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [home]
END


End;

/****** Object:  Default [DF__#__menu__langua__114A936A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__langua__114A936A]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__langua__114A936A]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Default [DF__#__menu__client__123EB7A3]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__menu__client__123EB7A3]') AND parent_object_id = OBJECT_ID(N'[#__menu]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__menu__client__123EB7A3]') AND type = 'D')
BEGIN
ALTER TABLE [#__menu] ADD  DEFAULT ((0)) FOR [client_id]
END


End;

SET IDENTITY_INSERT #__menu  ON;

INSERT INTO #__menu (id, menutype, title, alias, note, path, link,type, published,parent_id, level, component_id,ordering, checked_out, checked_out_time, browserNav,
  access, img, template_style_id, params, lft, rgt, home, language, client_id)
SELECT 1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '1900-01-01 00:00:00', 0, 0, '', 0, '', 0, 281, 0, '*', 0
UNION ALL
SELECT
2, 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 0, 1, 1, 4, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 13, 22, 0, '*', 1
UNION ALL
SELECT 3, 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 0, 2, 2, 4, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 14, 15, 0, '*', 1
UNION ALL
SELECT 4, 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 0, 2, 2, 6, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-cat', 0, '', 16, 17, 0, '*', 1
UNION ALL
SELECT 5, 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 0, 2, 2, 4, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-clients', 0, '', 18, 19, 0, '*', 1
UNION ALL
SELECT 6, 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 0, 2, 2, 4, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-tracks', 0, '', 20, 21, 0, '*', 1
UNION ALL
SELECT 7, 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 0, 1, 1, 8, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 23, 28, 0, '*', 1
UNION ALL
SELECT 8, 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 0, 7, 2, 8, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 24, 25, 0, '*', 1
UNION ALL
SELECT 9, 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 0, 7, 2, 6, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact-cat', 0, '', 26, 27, 0, '*', 1
UNION ALL
SELECT 10, 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 0, 1, 1, 15, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:messages', 0, '', 29, 34, 0, '*', 1
UNION ALL
SELECT 11, 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 0, 10, 2, 15, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:messages-add', 0, '', 30, 31, 0, '*', 1
UNION ALL
SELECT 12, 'menu', 'com_messages_read', 'Read Private Message', '', 'Messaging/Read Private Message', 'index.php?option=com_messages', 'component', 0, 10, 2, 15, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:messages-read', 0, '', 32, 33, 0, '*', 1
UNION ALL
SELECT 13, 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 1, 1, 17, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 35, 40, 0, '*', 1
UNION ALL
SELECT 14, 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 13, 2, 17, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 36, 37, 0, '*', 1
UNION ALL
SELECT 15, 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 0, 13, 2, 6, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds-cat', 0, '', 38, 39, 0, '*', 1
UNION ALL
SELECT 16, 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 0, 1, 1, 24, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:redirect', 0, '', 53, 54, 0, '*', 1
UNION ALL
SELECT 17, 'menu', 'com_search', 'Basic Search', '', 'Search', 'index.php?option=com_search', 'component', 0, 1, 1, 19, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:search', 0, '', 43, 44, 0, '*', 1
UNION ALL
SELECT 18, 'menu', 'com_weblinks', 'Weblinks', '', 'Weblinks', 'index.php?option=com_weblinks', 'component', 0, 1, 1, 21, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:weblinks', 0, '', 47, 52, 0, '*', 1
UNION ALL
SELECT 19, 'menu', 'com_weblinks_links', 'Links', '', 'Weblinks/Links', 'index.php?option=com_weblinks', 'component', 0, 18, 2, 21, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:weblinks', 0, '', 48, 49, 0, '*', 1
UNION ALL
SELECT 20, 'menu', 'com_weblinks_categories', 'Categories', '', 'Weblinks/Categories', 'index.php?option=com_categories&extension=com_weblinks', 'component', 0, 18, 2, 6, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:weblinks-cat', 0, '', 50, 51, 0, '*', 1
UNION ALL
SELECT 21, 'menu', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 0, 1, 1, 27, 0, 0, '1900-01-01 00:00:00', 0, 0, 'class:finder', 0, '', 41, 42, 0, '*', 1
UNION ALL
SELECT 101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, 22, 0, 0, '1900-01-01 00:00:00', 0, 1, '', 0, '{"featured_categories":[""],"num_leading_articles":"1","num_intro_articles":"3","num_columns":"3","num_links":"0","orderby_pri":"","orderby_sec":"front","order_date":"","multi_column_order":"1","show_pagination":"2","show_pagination_results":"1","show_noauth":"","article-allow_ratings":"","article-allow_comments":"","show_feed_link":"1","feed_summary":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_readmore":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","show_page_heading":1,"page_title":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 39, 40, 1, '*', 0;
SET IDENTITY_INSERT #__menu  OFF;


/****** Object:  Table [#__languages]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__languages]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__languages](
	[lang_id] [bigint] IDENTITY(1,1) NOT NULL,
	[lang_code] [nvarchar](7) NOT NULL,
	[title] [nvarchar](50) NOT NULL,
	[title_native] [nvarchar](50) NOT NULL,
	[sef] [nvarchar](50) NOT NULL,
	[image] [nvarchar](50) NOT NULL,
	[description] [nvarchar](512) NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[published] [int] NOT NULL,
	[ordering]  [int] NOT NULL,
	[sitename] [nvarchar] (1024) NOT NULL,
 CONSTRAINT [PK_#__languages_lang_id] PRIMARY KEY CLUSTERED 
(
	[lang_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__languages$idx_sef] UNIQUE NONCLUSTERED 
(
	[sef] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;


/****** Object:  Default [DF__#__langu__publi__02084FDA]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__langu__publi__02084FDA]') AND parent_object_id = OBJECT_ID(N'[#__languages]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__langu__publi__02084FDA]') AND type = 'D')
BEGIN
ALTER TABLE [#__languages] ADD  DEFAULT ((0)) FOR [published]
END


End;

SET IDENTITY_INSERT #__languages  ON;

INSERT INTO #__languages (lang_id,lang_code,title,title_native,sef,image,description,metakey,metadesc,sitename,published,ordering)
VALUES('1', 'en-GB', 'English (UK)', 'English (UK)', 'en', 'en', '', '', '', '', '1','1');

SET IDENTITY_INSERT #__languages  OFF;

/****** Object:  Table [#__extensions]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__extensions]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__extensions](
	[extension_id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[type] [nvarchar](20) NOT NULL,
	[element] [nvarchar](100) NOT NULL,
	[folder] [nvarchar](100) NOT NULL,
	[client_id] [smallint] NOT NULL,
	[enabled] [smallint] NOT NULL,
	[access] [int] NOT NULL,
	[protected] [smallint] NOT NULL,
	[manifest_cache] [nvarchar](max) NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[custom_data] [nvarchar](max) NOT NULL,
	[system_data] [nvarchar](max) NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
	[ordering] [int] NULL,
	[state] [int] NULL,
 CONSTRAINT [PK_#__extensions_extension_id] PRIMARY KEY CLUSTERED 
(
	[extension_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__extensions]') AND name = N'element_clientid')
CREATE NONCLUSTERED INDEX [element_clientid] ON [#__extensions] 
(
	[element] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__extensions]') AND name = N'element_folder_clientid')
CREATE NONCLUSTERED INDEX [element_folder_clientid] ON [#__extensions] 
(
	[element] ASC,
	[folder] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__extensions]') AND name = N'extension')
CREATE NONCLUSTERED INDEX [extension] ON [#__extensions] 
(
	[type] ASC,
	[element] ASC,
	[folder] ASC,
	[client_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__exten__enabl__7A672E12]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__exten__enabl__7A672E12]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__exten__enabl__7A672E12]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((1)) FOR [enabled]
END


End;

/****** Object:  Default [DF__#__exten__acces__7B5B524B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__exten__acces__7B5B524B]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__exten__acces__7B5B524B]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((1)) FOR [access]
END


End;

/****** Object:  Default [DF__#__exten__prote__7C4F7684]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__exten__prote__7C4F7684]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__exten__prote__7C4F7684]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [protected]
END


End;

/****** Object:  Default [DF__#__exten__check__7D439ABD]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__exten__check__7D439ABD]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__exten__check__7D439ABD]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__exten__check__7E37BEF6]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__exten__check__7E37BEF6]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__exten__check__7E37BEF6]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__exten__order__7F2BE32F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__exten__order__7F2BE32F]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__exten__order__7F2BE32F]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__exten__state__00200768]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__exten__state__00200768]') AND parent_object_id = OBJECT_ID(N'[#__extensions]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__exten__state__00200768]') AND type = 'D')
BEGIN
ALTER TABLE [#__extensions] ADD  DEFAULT ((0)) FOR [state]
END


End;



SET IDENTITY_INSERT #__extensions  ON;
INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
SELECT 1, 'com_mailto', 'component', 'com_mailto', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 2, 'com_wrapper', 'component', 'com_wrapper', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 3, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 4, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, '', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 5, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 6, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 7, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 8, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, '', '{"show_contact_category":"hide","show_contact_list":"0","presentation_style":"sliders","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","image":"","allow_vcard":"0","show_articles":"0","show_profile":"0","show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","show_headings":"1","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","allow_vcard_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_category_crumb":"0","metakey":"","metadesc":"","robots":"","author":"","rights":"","xreference":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 9, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 10, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 11, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, '', '{"administrator":"en-GB","site":"en-GB"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 12, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 13, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, '', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,application\\/x-shockwave-flash,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/x-zip","upload_mime_illegal":"text\\/html","enable_flash":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 14, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 15, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 16, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 17, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, '', '{"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_word_count":"0","show_headings":"1","show_name":"1","show_articles":"0","show_link":"1","show_description":"1","show_description_image":"1","display_num":"","show_pagination_limit":"1","show_pagination":"1","show_pagination_results":"1","show_cat_items":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 18, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 19, 'com_search', 'component', 'com_search', '', 1, 1, 1, 1, '', '{"enabled":"0","show_date":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 20, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 21, 'com_weblinks', 'component', 'com_weblinks', '', 1, 1, 1, 0, '', '{"show_comp_description":"1","comp_description":"","show_link_hits":"1","show_link_description":"1","show_other_cats":"0","show_headings":"0","show_numbers":"0","show_report":"1","count_clicks":"1","target":"0","link_icons":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 22, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, '{"legacy":false,"name":"com_content","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONTENT_XML_DESCRIPTION","group":""}', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","show_publishing_options":"1","show_article_options":"1","show_urls_images_frontend":"0","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_subcategory_content":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 23, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, '{"legacy":false,"name":"com_config","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONFIG_XML_DESCRIPTION","group":""}', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"NONE","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 24, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 25, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, '', '{"allowUserRegistration":"1","new_usertype":"2","useractivation":"1","frontend_userparams":"1","mailSubjectPrefix":"","mailBodySuffix":""}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 27, 'com_finder', 'component', 'com_finder', '', 1, 1, 0, 0, '', '{"show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_advanced":"1","expand_advanced":"0","show_date_filters":"0","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","memory_table_limit":30000,"title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stemmer":"porter_en"}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
SELECT 100, 'PHPMailer', 'library', 'phpmailer', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 101, 'SimplePie', 'library', 'simplepie', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0	   
UNION ALL
SELECT 103, 'Joomla! Web Application Framework', 'library', 'joomla', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:33:"Joomla! Web Application Framework";s:4:"type";s:7:"library";s:12:"creationDate";s:4:"2008";s:6:"author";s:6:"Joomla";s:9:"copyright";s:67:"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.";s:11:"authorEmail";s:16:"admin@joomla.org";s:9:"authorUrl";s:21:"http://www.joomla.org";s:7:"version";s:5:"1.6.0";s:11:"description";s:90:"The Joomla! Web Application Framework is the Core of the Joomla! Content Management System";s:5:"group";s:0:"";}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
SELECT 200, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 201, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 202, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 203, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 204, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 205, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 206, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 207, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
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
SELECT 216, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 217, 'mod_weblinks', 'module', 'mod_weblinks', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 218, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 219, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 220, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 221, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 222, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 223, 'mod_finder', 'module', 'mod_finder', '', 0, 1, 0, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
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
SELECT 306, 'mod_online', 'module', 'mod_online', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
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
SELECT 313, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, '{"legacy":false,"name":"mod_multilangstatus","type":"module","creationDate":"September 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"MOD_MULTILANGSTATUS_XML_DESCRIPTION","group":""}', '{"cache":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0



INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
SELECT 400, 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 401, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 402, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 403, 'plg_authentication_openid', 'plugin', 'openid', 'authentication', 0, 0, 1, 0, '', '{"usermode":"2","phishing-resistant":"0","multi-factor":"0","multi-factor-physical":"0"}', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 404, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '', '{"mode":"1"}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 405, 'plg_content_geshi', 'plugin', 'geshi', 'content', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 406, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '{"legacy":false,"name":"plg_content_loadmodule","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_LOADMODULE_XML_DESCRIPTION","group":""}', '{"style":"xhtml"}', '', '', 0, '2011-09-18 15:22:50', 0, 0
UNION ALL
SELECT 407, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 1, '', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 408, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 1, '', '{"position":"1"}', '', '', 0, '1900-01-01 00:00:00', 5, 0
UNION ALL
SELECT 409, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 6, 0
UNION ALL
SELECT 410, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '', '{"linenumbers":"0","tabmode":"indent"}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 411, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 412, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 1, '', '{"mode":"1","skin":"0","compressed":"0","cleanup_startup":"0","cleanup_save":"2","entity_encoding":"raw","lang_mode":"0","lang_code":"en","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","invalid_elements":"script,applet,iframe","extended_elements":"","toolbar":"top","toolbar_align":"left","html_height":"550","html_width":"750","element_path":"1","fonts":"1","paste":"1","searchreplace":"1","insertdate":"1","format_date":"%Y-%m-%d","inserttime":"1","format_time":"%H:%M:%S","colors":"1","table":"1","smilies":"1","media":"1","hr":"1","directionality":"1","fullscreen":"1","style":"1","layer":"1","xhtmlxtras":"1","visualchars":"1","nonbreaking":"1","template":"1","blockquote":"1","wordcount":"1","advimage":"1","advlink":"1","autosave":"1","contextmenu":"1","inlinepopups":"1","safari":"0","custom_plugin":"","custom_button":""}', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 413, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 414, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 415, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 416, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 417, 'plg_search_categories', 'plugin', 'categories', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 418, 'plg_search_contacts', 'plugin', 'contacts', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 419, 'plg_search_content', 'plugin', 'content', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 420, 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 421, 'plg_search_weblinks', 'plugin', 'weblinks', 'search', 0, 1, 1, 0, '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 422, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 423, 'plg_system_p3p', 'plugin', 'p3p', 'system', 0, 1, 1, 1, '', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 424, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 1, '', '{"browsercache":"0","cachetime":"15"}', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 425, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 426, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 5, 0
UNION ALL
SELECT 427, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 6, 0
UNION ALL
SELECT 428, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 7, 0
UNION ALL
SELECT 429, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 8, 0
UNION ALL
SELECT 430, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 9, 0
UNION ALL
SELECT 431, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 1, '', '{"autowebpage":"","category":"26","autopublish":"0"}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 432, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '', '{"autoregister":"1"}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 433, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 1, '', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 434, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 436, 'plg_system_languagecode', 'plugin', 'languagecode', 'system', 0, 0, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 10, 0
UNION ALL
SELECT 437, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 438, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 1, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 439, 'plg_captcha_recaptcha', 'plugin', 'recaptcha', 'captcha', 0, 1, 1, 0, '{}', '{"public_key":"","private_key":"","theme":"clean"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 440, 'plg_system_highlight', 'plugin', 'highlight', 'system', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 7, 0
UNION ALL
SELECT 441, 'plg_content_finder', 'plugin', 'finder', 'content', 0, 0, 1, 0, '{"legacy":false,"name":"plg_content_finder","type":"plugin","creationDate":"December 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_FINDER_XML_DESCRIPTION","group":""}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 442, 'plg_finder_categories', 'plugin', 'categories', 'finder', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 1, 0
UNION ALL
SELECT 443, 'plg_finder_contacts', 'plugin', 'contacts', 'finder', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 2, 0
UNION ALL
SELECT 444, 'plg_finder_content', 'plugin', 'content', 'finder', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 3, 0
UNION ALL
SELECT 445, 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 4, 0
UNION ALL
SELECT 446, 'plg_finder_weblinks', 'plugin', 'weblinks', 'finder', 0, 1, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 5, 0
UNION ALL
SELECT 447, 'System - Windows Azure', 'plugin', 'plg_azure', 'system', 0, 0, 1, 0, '', '{"protocol":"=http\n"}', '', '', 0, '1900-01-01 00:00:00', 1, 0;




INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
SELECT 500, 'atomic', 'template', 'atomic', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:6:"atomic";s:4:"type";s:8:"template";s:12:"creationDate";s:8:"10/10/09";s:6:"author";s:12:"Joomla! Project";s:9:"copyright";s:72:"Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:25:"contact@kontentdesign.com";s:9:"authorUrl";s:28:"http://www.kontentdesign.com";s:7:"version";s:5:"1.6.0";s:11:"description";s:26:"TPL_ATOMIC_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 502, 'bluestork', 'template', 'bluestork', '', 1, 1, 1, 0, '{"legacy":false,"name":"bluestork","type":"template","creationDate":"07\\/02\\/09","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"http:\\/\\/www.joomla.org","version":"1.6.0","description":"TPL_BLUESTORK_XML_DESCRIPTION","group":""}', '{"useRoundedCorners":"1","showSiteName":"0","textBig":"0","highContrast":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 503, 'beez_20', 'template', 'beez_20', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:7:"beez_20";s:4:"type";s:8:"template";s:12:"creationDate";s:16:"25 November 2009";s:6:"author";s:12:"Angie Radtke";s:9:"copyright";s:72:"Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:23:"a.radtke@derauftritt.de";s:9:"authorUrl";s:26:"http://www.der-auftritt.de";s:7:"version";s:5:"1.6.0";s:11:"description";s:25:"TPL_BEEZ2_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:6:"hathor";s:4:"type";s:8:"template";s:12:"creationDate";s:8:"May 2010";s:6:"author";s:11:"Andrea Tarr";s:9:"copyright";s:72:"Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:25:"hathor@tarrconsulting.com";s:9:"authorUrl";s:29:"http://www.tarrconsulting.com";s:7:"version";s:5:"1.6.0";s:11:"description";s:26:"TPL_HATHOR_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 505, 'beez5', 'template', 'beez5', '', 0, 1, 1, 0, 'a:11:{s:6:"legacy";b:0;s:4:"name";s:5:"beez5";s:4:"type";s:8:"template";s:12:"creationDate";s:11:"21 May 2010";s:6:"author";s:12:"Angie Radtke";s:9:"copyright";s:72:"Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.";s:11:"authorEmail";s:23:"a.radtke@derauftritt.de";s:9:"authorUrl";s:26:"http://www.der-auftritt.de";s:7:"version";s:5:"1.6.0";s:11:"description";s:25:"TPL_BEEZ5_XML_DESCRIPTION";s:5:"group";s:0:"";}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","html5":"0"}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
SELECT 600, 'English (United Kingdom)', 'language', 'en-GB', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 601, 'English (United Kingdom)', 'language', 'en-GB', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;


INSERT INTO #__extensions (extension_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) 
VALUES (700, 'Joomla! CMS', 'file', 'joomla', '', 0, 1, 1, 1, '{"legacy":false,"name":"files_joomla","type":"file","creationDate":"December 2011","author":"Joomla!","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"2.5.4","description":"FILES_JOOMLA_XML_DESCRIPTION","group":""}', '', '', '', 0, '1900-01-01 00:00:00', 0, 0);

INSERT INTO #__extensions (extension_id, name,type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state) VALUES
(800, 'joomla', 'package', 'pkg_joomla', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0);
SET IDENTITY_INSERT #__extensions  OFF;



SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_types]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_types](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[mime] [nvarchar](100) NOT NULL,
 CONSTRAINT [PK_#__finder_types_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__finder_types$title] UNIQUE NONCLUSTERED 
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;


SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_tokens_aggregate](
	[term_id] [bigint]  NOT NULL,
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]') AND name = N'keyword_id')
CREATE NONCLUSTERED INDEX [keyword_id] ON [#__finder_tokens_aggregate] 
(
	[term_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]') AND name = N'token')
CREATE NONCLUSTERED INDEX [token] ON [#__finder_tokens_aggregate] 
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__commo__3587F3E0]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__commo__3587F3E0]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens_aggregate] ADD  DEFAULT ((0)) FOR [common]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__phras__367C1819]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__phras__367C1819]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens_aggregate] ADD  DEFAULT ((0)) FOR [phrase]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__conte__37703C52]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens_aggregate]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__conte__37703C52]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens_aggregate] ADD  DEFAULT ((2)) FOR [context]
END

End;


SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens]') AND name = N'idx_context')
CREATE NONCLUSTERED INDEX [idx_context] ON [#__finder_tokens] 
(
	[context] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_tokens]') AND name = N'idx_word')
CREATE NONCLUSTERED INDEX [idx_word] ON [#__finder_tokens] 
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)




IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__commo__30C33EC3]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__commo__30C33EC3]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((0)) FOR [common]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__phras__31B762FC]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__phras__31B762FC]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((0)) FOR [phrase]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__weigh__32AB8735]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__weigh__32AB8735]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((1)) FOR [weight]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__conte__339FAB6E]') AND parent_object_id = OBJECT_ID(N'[#__finder_tokens]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__conte__339FAB6E]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_tokens] ADD  DEFAULT ((2)) FOR [context]
END

End;


SET QUOTED_IDENTIFIER ON


SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_terms_common]') AND type in (N'U'))
BEGIN

CREATE TABLE [#__finder_terms_common](
	[term] [nvarchar](75)  NOT NULL,
	[language] [nvarchar](3) NOT NULL,
		 CONSTRAINT [PK_#__finder_terms_common] PRIMARY KEY CLUSTERED 
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)

END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms_common]') AND name = N'idx_lang')
CREATE NONCLUSTERED INDEX [idx_lang] ON [#__finder_terms_common] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms_common]') AND name = N'idx_word_lang')
CREATE NONCLUSTERED INDEX [idx_word_lang] ON [#__finder_terms_common] 
(
	[term] ASC,
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('a', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('about', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('after', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ago', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('all', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('am', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ad', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ai', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ay', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('are', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('are''t', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('as', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('at', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('be', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('but', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('by', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('for', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('from', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('get', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('go', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('how', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('if', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('i', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ito', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('is', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('is''t', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('it', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('its', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('me', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('more', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('most', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('must', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('my', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ew', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('o', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('oe', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ot', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('oth', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('othig', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('of', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('off', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ofte', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('old', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('oc', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('oce', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('oli', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('oly', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('or', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('other', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('our', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('ours', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('out', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('over', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('page', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('she', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('should', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('small', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('so', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('some', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('tha', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('thak', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('that', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('the', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('their', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('theirs', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('them', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('there', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('these', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('they', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('this', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('those', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('thus', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('time', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('times', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('to', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('too', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('true', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('uder', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('util', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('up', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('upo', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('use', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('user', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('users', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('veri', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('versio', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('very', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('via', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('wat', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('was', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('way', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('were', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('what', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('whe', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('where', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('whi', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('which', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('who', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('whom', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('whose', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('why', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('wide', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('will', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('with', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('withi', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('without', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('would', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('yes', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('yet', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('you', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('your', 'e');
INSERT INTO[#__finder_terms_common] ([term], [language]) VALUES ('yours', 'e');


SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'#__finder_terms]') AND type in (N'U'))
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
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__finder_terms$idx_term] UNIQUE NONCLUSTERED 
(
	[term] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms]') AND name = N'idx_soundex_phrase')
CREATE NONCLUSTERED INDEX [idx_soundex_phrase] ON [#__finder_terms] 
(
	[soundex] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms]') AND name = N'idx_stem_phrase')
CREATE NONCLUSTERED INDEX [idx_stem_phrase] ON [#__finder_terms] 
(
	[stem] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_terms]') AND name = N'idx_term_phrase')
CREATE NONCLUSTERED INDEX [idx_term_phrase] ON [#__finder_terms] 
(
	[term] ASC,
	[phrase] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__commo__2B0A656D]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__commo__2B0A656D]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [common]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__phras__2BFE89A6]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__phras__2BFE89A6]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [phrase]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__weigh__2CF2ADDF]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__weigh__2CF2ADDF]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [weight]
END

End;


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__links__2DE6D218]') AND parent_object_id = OBJECT_ID(N'[#__finder_terms]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__links__2DE6D218]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_terms] ADD  DEFAULT ((0)) FOR [links]
END

End;


SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy_map]') AND name = N'link_id')
CREATE NONCLUSTERED INDEX [link_id] ON [#__finder_taxonomy_map] 
(
	[link_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy_map]') AND name = N'node_id')
CREATE NONCLUSTERED INDEX [node_id] ON [#__finder_taxonomy_map] 
(
	[node_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'access')
CREATE NONCLUSTERED INDEX [access] ON [#__finder_taxonomy] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'idx_parent_published')
CREATE NONCLUSTERED INDEX [idx_parent_published] ON [#__finder_taxonomy] 
(
	[parent_id] ASC,
	[state] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'ordering')
CREATE NONCLUSTERED INDEX [ordering] ON [#__finder_taxonomy] 
(
	[ordering] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'parent_id')
CREATE NONCLUSTERED INDEX [parent_id] ON [#__finder_taxonomy] 
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_taxonomy]') AND name = N'state')
CREATE NONCLUSTERED INDEX [state] ON [#__finder_taxonomy] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__paren__25518C17]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__paren__25518C17]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((0)) FOR [parent_id]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__state__2645B050]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__state__2645B050]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((1)) FOR [state]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__acces__2739D489]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__acces__2739D489]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((0)) FOR [access]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__order__282DF8C2]') AND parent_object_id = OBJECT_ID(N'[#__finder_taxonomy]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__order__282DF8C2]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_taxonomy] ADD  DEFAULT ((0)) FOR [ordering]
END

End;

SET IDENTITY_INSERT [#__finder_taxonomy] ON
INSERT INTO[#__finder_taxonomy] ([id], [parent_id], [title], [state], [access], [ordering]) VALUES ('1', '0', 'ROOT', '0', '0', '0');
SET IDENTITY_INSERT [#__finder_taxonomy] OFF;



SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsf]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsf] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsf]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsf] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



SET QUOTED_IDENTIFIER ON
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termse]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termse] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termse]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [dbo].[#__finder_links_termse] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsd]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsd] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsd]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsd] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsc]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsc] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsc]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsc] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)




SET QUOTED_IDENTIFIER ON
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsb]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsb] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsb]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsb] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsa]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_termsa] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_termsa]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_termsa] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)




SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms9]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms9] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms9]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms9] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms8]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms8] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms8]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms8] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms7]') AND type in (N'U'))
BEGIN
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms7]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms7] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms7]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms7] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms6]') AND type in (N'U'))
BEGIN
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms6]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms6] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms6]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms6] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms5]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms5] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms5]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON .[#__finder_links_terms5] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms4]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms4] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms4]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms4] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms3]') AND type in (N'U'))
BEGIN
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms3]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms3] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms3]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms3] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links_terms2]') AND type in (N'U'))
BEGIN
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms2]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms2] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms2]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms2] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms1]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms1] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms1]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms1] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)


SET QUOTED_IDENTIFIER ON

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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms0]') AND name = N'idx_link_term_weight')
CREATE NONCLUSTERED INDEX [idx_link_term_weight] ON [#__finder_links_terms0] 
(
	[link_id] ASC,
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links_terms0]') AND name = N'idx_term_weight')
CREATE NONCLUSTERED INDEX [idx_term_weight] ON [#__finder_links_terms0] 
(
	[term_id] ASC,
	[weight] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)




SET QUOTED_IDENTIFIER ON

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__finder_links](
	[link_id] [bigint] IDENTITY(1,1) NOT NULL,
	[url] [nvarchar](255) NOT NULL,
	[route] [nvarchar](255) NOT NULL,
	[title] [nvarchar](255) NULL,
	[description] [nvarchar](max) NULL,
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
	[object] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__finder_links_link_id] PRIMARY KEY CLUSTERED 
(
	[link_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_md5')
CREATE NONCLUSTERED INDEX [idx_md5] ON [#__finder_links] 
(
	[md5sum] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_published_list')
CREATE NONCLUSTERED INDEX [idx_published_list] ON [#__finder_links] 
(
	[published] ASC,
	[state] ASC,
	[access] ASC,
	[publish_start_date] ASC,
	[publish_end_date] ASC,
	[list_price] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_published_sale')
CREATE NONCLUSTERED INDEX [idx_published_sale] ON [#__finder_links] 
(
	[published] ASC,
	[state] ASC,
	[access] ASC,
	[publish_start_date] ASC,
	[publish_end_date] ASC,
	[sale_price] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_title')
CREATE NONCLUSTERED INDEX [idx_title] ON [#__finder_links] 
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_type')
CREATE NONCLUSTERED INDEX [idx_type] ON [#__finder_links] 
(
	[type_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__finder_links]') AND name = N'idx_url')
CREATE NONCLUSTERED INDEX [idx_url] ON [#__finder_links] 
(
	[url] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)



IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__title__08B54D69]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__title__08B54D69]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (NULL) FOR [title]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__descr__09A971A2]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__descr__09A971A2]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (NULL) FOR [description]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__index__0A9D95DB]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__index__0A9D95DB]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (getdate()) FOR [indexdate]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__md5su__0B91BA14]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__md5su__0B91BA14]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT (NULL) FOR [md5sum]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__publi__0C85DE4D]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__publi__0C85DE4D]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((1)) FOR [published]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__state__0D7A0286]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__state__0D7A0286]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((1)) FOR [state]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__acces__0E6E26BF]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__acces__0E6E26BF]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((0)) FOR [access]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__publi__0F624AF8]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__publi__0F624AF8]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_start_date]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__publi__10566F31]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__publi__10566F31]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_end_date]
END

End;


IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__start__114A936A]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__start__114A936A]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [start_date]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__end_d__123EB7A3]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__end_d__123EB7A3]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [end_date]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__list___1332DBDC]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__list___1332DBDC]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((0)) FOR [list_price]
END

End;

IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__sale___14270015]') AND parent_object_id = OBJECT_ID(N'[#__finder_links]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__sale___14270015]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_links] ADD  DEFAULT ((0)) FOR [sale_price]
END

End;


SET QUOTED_IDENTIFIER ON

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

/****** Object:  Default [DF__#__finde__state__01142BA1]    Script Date: 12/30/2011 16:12:17 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__state__01142BA1]') AND parent_object_id = OBJECT_ID(N'[#__finder_filters]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__state__01142BA1]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((1)) FOR [state]
END

End;
/****** Object:  Default [DF__#__finde__creat__02084FDA]    Script Date: 12/30/2011 16:12:17 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__creat__02084FDA]') AND parent_object_id = OBJECT_ID(N'[#__finder_filters]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__creat__02084FDA]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created]
END

End;
/****** Object:  Default [DF__#__finde__modif__02FC7413]    Script Date: 12/30/2011 16:12:17 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__modif__02FC7413]') AND parent_object_id = OBJECT_ID(N'[#__finder_filters]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__modif__02FC7413]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [modified]
END

End;
/****** Object:  Default [DF__#__finde__modif__03F0984C]    Script Date: 12/30/2011 16:12:17 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__modif__03F0984C]') AND parent_object_id = OBJECT_ID(N'[#__finder_filters]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__modif__03F0984C]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((0)) FOR [modified_by]
END

End;
/****** Object:  Default [DF__#__finde__check__04E4BC85]    Script Date: 12/30/2011 16:12:17 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__check__04E4BC85]') AND parent_object_id = OBJECT_ID(N'[#__finder_filters]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__check__04E4BC85]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((0)) FOR [checked_out]
END

End;
/****** Object:  Default [DF__#__finde__check__05D8E0BE]    Script Date: 12/30/2011 16:12:17 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__check__05D8E0BE]') AND parent_object_id = OBJECT_ID(N'[#__finder_filters]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__check__05D8E0BE]') AND type = 'D')
BEGIN

ALTER TABLE [#__finder_filters] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END

End;
/****** Object:  Default [DF__#__finde__map_c__06CD04F7]    Script Date: 12/30/2011 16:12:17 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__finde__map_c__06CD04F7]') AND parent_object_id = OBJECT_ID(N'[#__finder_filters]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__finde__map_c__06CD04F7]') AND type = 'D')
BEGIN
ALTER TABLE [#__finder_filters] ADD  DEFAULT ((0)) FOR [map_count]
END

End;


/****** Object:  Table [#__core_log_searches]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__core_log_searches]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__core_log_searches](
	[search_term] [nvarchar](128) NOT NULL,
	[hits] [bigint] NOT NULL
)
END;

/****** Object:  Default [DF__#__core___searc__778AC167]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__core___searc__778AC167]') AND parent_object_id = OBJECT_ID(N'[#__core_log_searches]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__core___searc__778AC167]') AND type = 'D')
BEGIN
ALTER TABLE [#__core_log_searches] ADD  DEFAULT (N'') FOR [search_term]
END


End;

/****** Object:  Default [DF__#__core_l__hits__787EE5A0]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__core_l__hits__787EE5A0]') AND parent_object_id = OBJECT_ID(N'[#__core_log_searches]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__core_l__hits__787EE5A0]') AND type = 'D')
BEGIN
ALTER TABLE [#__core_log_searches] ADD  DEFAULT ((0)) FOR [hits]
END


End;


/****** Object:  Table [#__content_rating]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__content_rating]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__content_rating](
	[content_id] [int] NOT NULL,
	[rating_sum] [bigint] NOT NULL,
	[rating_count] [bigint] NOT NULL,
	[lastip] [nvarchar](50) NOT NULL,
 CONSTRAINT [PK_#__content_rating_content_id] PRIMARY KEY CLUSTERED 
(
	[content_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__conte__conte__72C60C4A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__conte__72C60C4A]') AND parent_object_id = OBJECT_ID(N'[#__content_rating]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__conte__72C60C4A]') AND type = 'D')
BEGIN
ALTER TABLE [#__content_rating] ADD  DEFAULT ((0)) FOR [content_id]
END


End;

/****** Object:  Default [DF__#__conte__ratin__73BA3083]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__ratin__73BA3083]') AND parent_object_id = OBJECT_ID(N'[#__content_rating]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__ratin__73BA3083]') AND type = 'D')
BEGIN
ALTER TABLE [#__content_rating] ADD  DEFAULT ((0)) FOR [rating_sum]
END


End;

/****** Object:  Default [DF__#__conte__ratin__74AE54BC]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__ratin__74AE54BC]') AND parent_object_id = OBJECT_ID(N'[#__content_rating]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__ratin__74AE54BC]') AND type = 'D')
BEGIN
ALTER TABLE [#__content_rating] ADD  DEFAULT ((0)) FOR [rating_count]
END


End;

/****** Object:  Default [DF__#__conte__lasti__75A278F5]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__lasti__75A278F5]') AND parent_object_id = OBJECT_ID(N'[#__content_rating]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__lasti__75A278F5]') AND type = 'D')
BEGIN
ALTER TABLE [#__content_rating] ADD  DEFAULT (N'') FOR [lastip]
END


End;


/****** Object:  Table [#__content_frontpage]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__content_frontpage]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__content_frontpage](
	[content_id] [int] NOT NULL,
	[ordering] [int] NOT NULL,
 CONSTRAINT [PK_#__content_frontpage_content_id] PRIMARY KEY CLUSTERED 
(
	[content_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

/****** Object:  Default [DF__#__conte__conte__6FE99F9F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__conte__6FE99F9F]') AND parent_object_id = OBJECT_ID(N'[#__content_frontpage]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__conte__6FE99F9F]') AND type = 'D')
BEGIN
ALTER TABLE [#__content_frontpage] ADD  DEFAULT ((0)) FOR [content_id]
END


End;

/****** Object:  Default [DF__#__conte__order__70DDC3D8]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__order__70DDC3D8]') AND parent_object_id = OBJECT_ID(N'[#__content_frontpage]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__order__70DDC3D8]') AND type = 'D')
BEGIN
ALTER TABLE [#__content_frontpage] ADD  DEFAULT ((0)) FOR [ordering]
END


End;


/****** Object:  Table [#__content]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__content]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__content](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
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
	[created] [datetime] NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime] NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
	[publish_up] [datetime] NOT NULL,
	[publish_down] [datetime] NOT NULL,
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
	[language] [nvarchar](7) NOT NULL,
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
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_catid')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__content] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_checkout')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__content] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_createdby')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__content] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_featured_catid')
CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__content] 
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__content] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_state')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__content] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__content]') AND name = N'idx_xreference')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__content] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__conte__asset__59063A47]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__asset__59063A47]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__asset__59063A47]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [asset_id]
END


End;

/****** Object:  Default [DF__#__conte__title__59FA5E80]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__title__59FA5E80]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__title__59FA5E80]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [title]
END


End;

/****** Object:  Default [DF__#__conte__alias__5AEE82B9]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__alias__5AEE82B9]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__alias__5AEE82B9]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__conte__title__5BE2A6F2]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__title__5BE2A6F2]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__title__5BE2A6F2]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [title_alias]
END


End;

/****** Object:  Default [DF__#__conte__state__5CD6CB2B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__state__5CD6CB2B]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__state__5CD6CB2B]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__conte__secti__5DCAEF64]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__secti__5DCAEF64]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__secti__5DCAEF64]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [sectionid]
END


End;

/****** Object:  Default [DF__#__conten__mask__5EBF139D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conten__mask__5EBF139D]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conten__mask__5EBF139D]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [mask]
END


End;

/****** Object:  Default [DF__#__conte__catid__5FB337D6]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__catid__5FB337D6]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__catid__5FB337D6]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__conte__creat__60A75C0F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__creat__60A75C0F]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__creat__60A75C0F]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created]
END


End;

/****** Object:  Default [DF__#__conte__creat__619B8048]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__creat__619B8048]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__creat__619B8048]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [created_by]
END


End;

/****** Object:  Default [DF__#__conte__creat__628FA481]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__creat__628FA481]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__creat__628FA481]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT (N'') FOR [created_by_alias]
END


End;

/****** Object:  Default [DF__#__conte__modif__6383C8BA]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__modif__6383C8BA]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__modif__6383C8BA]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [modified]
END


End;

/****** Object:  Default [DF__#__conte__modif__6477ECF3]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__modif__6477ECF3]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__modif__6477ECF3]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__conte__check__656C112C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__check__656C112C]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__check__656C112C]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__conte__check__66603565]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__check__66603565]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__check__66603565]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__conte__publi__6754599E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__publi__6754599E]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__publi__6754599E]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__conte__publi__68487DD7]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__publi__68487DD7]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__publi__68487DD7]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_down]
END


End;

/****** Object:  Default [DF__#__conte__versi__693CA210]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__versi__693CA210]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__versi__693CA210]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((1)) FOR [version]
END


End;

/****** Object:  Default [DF__#__conte__paren__6A30C649]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__paren__6A30C649]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__paren__6A30C649]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [parentid]
END


End;

/****** Object:  Default [DF__#__conte__order__6B24EA82]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__order__6B24EA82]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__order__6B24EA82]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__conte__acces__6C190EBB]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__acces__6C190EBB]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__acces__6C190EBB]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__conten__hits__6D0D32F4]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conten__hits__6D0D32F4]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conten__hits__6D0D32F4]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [hits]
END


End;

/****** Object:  Default [DF__#__conte__featu__6E01572D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conte__featu__6E01572D]') AND parent_object_id = OBJECT_ID(N'[#__content]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conte__featu__6E01572D]') AND type = 'D')
BEGIN
ALTER TABLE [#__content] ADD  DEFAULT ((0)) FOR [featured]
END


End;


/****** Object:  Table [#__contact_details]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__contact_details](
	[id] [int] IDENTITY(1,1) NOT NULL,
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
	[checked_out_time] [datetime] NOT NULL,
	[ordering] [int] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[user_id] [int] NOT NULL,
	[catid] [int] NOT NULL,
	[access] [int] NOT NULL,
	[mobile] [nvarchar](255) NOT NULL,
	[webpage] [nvarchar](255) NOT NULL,
	[sortname1] [nvarchar](255) NOT NULL,
	[sortname2] [nvarchar](255) NOT NULL,
	[sortname3] [nvarchar](255) NOT NULL,
	[language] [nvarchar](7) NOT NULL,
	[created] [datetime] NOT NULL,
	[created_by] [bigint] NOT NULL,
	[created_by_alias] [nvarchar](255) NOT NULL,
	[modified] [datetime] NOT NULL,
	[modified_by] [bigint] NOT NULL,
	[metakey] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](max) NOT NULL,
	[metadata] [nvarchar](max) NOT NULL,
	[featured] [tinyint] NOT NULL,
	[xreference] [nvarchar](50) NOT NULL,
	[publish_up] [datetime] NOT NULL,
	[publish_down] [datetime] NOT NULL,
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
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_catid')
CREATE NONCLUSTERED INDEX [idx_catid] ON [#__contact_details] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_checkout')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__contact_details] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_createdby')
CREATE NONCLUSTERED INDEX [idx_createdby] ON [#__contact_details] 
(
	[created_by] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_featured_catid')
CREATE NONCLUSTERED INDEX [idx_featured_catid] ON [#__contact_details] 
(
	[featured] ASC,
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__contact_details] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_state')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__contact_details] 
(
	[published] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__contact_details]') AND name = N'idx_xreference')
CREATE NONCLUSTERED INDEX [idx_xreference] ON [#__contact_details] 
(
	[xreference] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__contac__name__3B75D760]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__contac__name__3B75D760]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__contac__name__3B75D760]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__conta__alias__3C69FB99]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__alias__3C69FB99]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__alias__3C69FB99]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__conta__con_p__3D5E1FD2]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__con_p__3D5E1FD2]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__con_p__3D5E1FD2]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [con_position]
END


End;

/****** Object:  Default [DF__#__conta__subur__3E52440B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__subur__3E52440B]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__subur__3E52440B]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [suburb]
END


End;

/****** Object:  Default [DF__#__conta__state__3F466844]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__state__3F466844]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__state__3F466844]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [state]
END


End;

/****** Object:  Default [DF__#__conta__count__403A8C7D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__count__403A8C7D]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__count__403A8C7D]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [country]
END


End;

/****** Object:  Default [DF__#__conta__postc__412EB0B6]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__postc__412EB0B6]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__postc__412EB0B6]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [postcode]
END


End;

/****** Object:  Default [DF__#__conta__telep__4222D4EF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__telep__4222D4EF]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__telep__4222D4EF]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [telephone]
END


End;

/****** Object:  Default [DF__#__contact__fax__4316F928]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__contact__fax__4316F928]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__contact__fax__4316F928]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [fax]
END


End;

/****** Object:  Default [DF__#__conta__image__440B1D61]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__image__440B1D61]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__image__440B1D61]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [image]
END


End;

/****** Object:  Default [DF__#__conta__image__44FF419A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__image__44FF419A]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__image__44FF419A]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [imagepos]
END


End;

/****** Object:  Default [DF__#__conta__email__45F365D3]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__email__45F365D3]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__email__45F365D3]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (NULL) FOR [email_to]
END


End;

/****** Object:  Default [DF__#__conta__defau__46E78A0C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__defau__46E78A0C]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__defau__46E78A0C]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [default_con]
END


End;

/****** Object:  Default [DF__#__conta__publi__47DBAE45]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__publi__47DBAE45]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__publi__47DBAE45]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__conta__check__48CFD27E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__check__48CFD27E]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__check__48CFD27E]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__conta__check__49C3F6B7]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__check__49C3F6B7]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__check__49C3F6B7]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__conta__order__4AB81AF0]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__order__4AB81AF0]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__order__4AB81AF0]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__conta__user___4BAC3F29]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__user___4BAC3F29]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__user___4BAC3F29]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [user_id]
END


End;

/****** Object:  Default [DF__#__conta__catid__4CA06362]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__catid__4CA06362]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__catid__4CA06362]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__conta__acces__4D94879B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__acces__4D94879B]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__acces__4D94879B]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__conta__mobil__4E88ABD4]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__mobil__4E88ABD4]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__mobil__4E88ABD4]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [mobile]
END


End;

/****** Object:  Default [DF__#__conta__webpa__4F7CD00D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__webpa__4F7CD00D]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__webpa__4F7CD00D]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [webpage]
END


End;

/****** Object:  Default [DF__#__conta__creat__5070F446]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__creat__5070F446]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__creat__5070F446]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created]
END


End;

/****** Object:  Default [DF__#__conta__creat__5165187F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__creat__5165187F]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__creat__5165187F]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [created_by]
END


End;

/****** Object:  Default [DF__#__conta__creat__52593CB8]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__creat__52593CB8]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__creat__52593CB8]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT (N'') FOR [created_by_alias]
END


End;

/****** Object:  Default [DF__#__conta__modif__534D60F1]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__modif__534D60F1]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__modif__534D60F1]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [modified]
END


End;

/****** Object:  Default [DF__#__conta__modif__5441852A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__modif__5441852A]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__modif__5441852A]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [modified_by]
END


End;

/****** Object:  Default [DF__#__conta__featu__5535A963]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__featu__5535A963]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__featu__5535A963]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ((0)) FOR [featured]
END


End;

/****** Object:  Default [DF__#__conta__publi__5629CD9C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__publi__5629CD9C]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__publi__5629CD9C]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__conta__publi__571DF1D5]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__conta__publi__571DF1D5]') AND parent_object_id = OBJECT_ID(N'[#__contact_details]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__conta__publi__571DF1D5]') AND type = 'D')
BEGIN
ALTER TABLE [#__contact_details] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_down]
END


End;


/****** Object:  Table [#__categories]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__categories]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__categories](
	[id] [int] IDENTITY(1,1) NOT NULL,
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
	[checked_out_time] [datetime] NOT NULL,
	[access] [int] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
	[metadesc] [nvarchar](1024) NOT NULL,
	[metakey] [nvarchar](1024) NOT NULL,
	[metadata] [nvarchar](2048) NOT NULL,
	[created_user_id] [bigint] NOT NULL,
	[created_time] [datetime] NOT NULL,
	[modified_user_id] [bigint] NOT NULL,
	[modified_time] [datetime] NOT NULL,
	[hits] [bigint] NOT NULL,
	[language] [nvarchar](7) NOT NULL,
 CONSTRAINT [PK_#__categories_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'cat_idx')
CREATE NONCLUSTERED INDEX [cat_idx] ON [#__categories] 
(
	[extension] ASC,
	[published] ASC,
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_access')
CREATE NONCLUSTERED INDEX [idx_access] ON [#__categories] 
(
	[access] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_alias')
CREATE NONCLUSTERED INDEX [idx_alias] ON [#__categories] 
(
	[alias] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_checkout')
CREATE NONCLUSTERED INDEX [idx_checkout] ON [#__categories] 
(
	[checked_out] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__categories] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_left_right')
CREATE NONCLUSTERED INDEX [idx_left_right] ON [#__categories] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_path')
CREATE NONCLUSTERED INDEX [idx_path] ON [#__categories] 
(
	[path] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_created_user_id')
CREATE NONCLUSTERED INDEX [idx_created_user_id] ON [#__categories] 
(
	[created_user_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_checked_out_time')
CREATE NONCLUSTERED INDEX [idx_checked_out_time] ON [#__categories] 
(
	[checked_out_time] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__categories]') AND name = N'idx_asset_id')
CREATE NONCLUSTERED INDEX [idx_asset_id] ON [#__categories] 
(
	[asset_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__categ__asset__276EDEB3]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__asset__276EDEB3]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__asset__276EDEB3]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [asset_id]
END


End;

/****** Object:  Default [DF__#__categ__paren__286302EC]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__paren__286302EC]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__paren__286302EC]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__cater__lft__29572725]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__cater__lft__29572725]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__cater__lft__29572725]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__cater__rgt__2A4B4B5E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__cater__rgt__2A4B4B5E]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__cater__rgt__2A4B4B5E]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [rgt]
END


End;

/****** Object:  Default [DF__#__categ__level__2B3F6F97]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__level__2B3F6F97]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__level__2B3F6F97]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [level]
END


End;

/****** Object:  Default [DF__#__cate__path__2C3393D0]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__cate__path__2C3393D0]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__cate__path__2C3393D0]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [path]
END


End;

/****** Object:  Default [DF__#__categ__exten__2D27B809]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__exten__2D27B809]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__exten__2D27B809]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [extension]
END


End;

/****** Object:  Default [DF__#__categ__alias__2E1BDC42]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__alias__2E1BDC42]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__alias__2E1BDC42]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__cate__note__2F10007B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__cate__note__2F10007B]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__cate__note__2F10007B]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [note]
END


End;

/****** Object:  Default [DF__#__categ__descr__300424B4]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__descr__300424B4]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__descr__300424B4]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [description]
END


End;

/****** Object:  Default [DF__#__categ__publi__30F848ED]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__publi__30F848ED]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__publi__30F848ED]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [published]
END


End;

/****** Object:  Default [DF__#__categ__check__31EC6D26]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__check__31EC6D26]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__check__31EC6D26]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__categ__check__32E0915F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__check__32E0915F]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__check__32E0915F]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__categ__acces__33D4B598]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__acces__33D4B598]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__acces__33D4B598]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [access]
END


End;

/****** Object:  Default [DF__#__categ__param__34C8D9D1]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__param__34C8D9D1]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__param__34C8D9D1]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT (N'') FOR [params]
END


End;

/****** Object:  Default [DF__#__categ__creat__35BCFE0A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__creat__35BCFE0A]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__creat__35BCFE0A]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [created_user_id]
END


End;

/****** Object:  Default [DF__#__categ__creat__36B12243]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__creat__36B12243]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__creat__36B12243]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created_time]
END


End;

/****** Object:  Default [DF__#__categ__modif__37A5467C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__modif__37A5467C]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__modif__37A5467C]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [modified_user_id]
END


End;

/****** Object:  Default [DF__#__categ__modif__38996AB5]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__categ__modif__38996AB5]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__categ__modif__38996AB5]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [modified_time]
END


End;

/****** Object:  Default [DF__#__cate__hits__398D8EEE]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__cate__hits__398D8EEE]') AND parent_object_id = OBJECT_ID(N'[#__categories]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__cate__hits__398D8EEE]') AND type = 'D')
BEGIN
ALTER TABLE [#__categories] ADD  DEFAULT ((0)) FOR [hits]
END


End;

SET IDENTITY_INSERT #__categories  ON;

INSERT INTO #__categories (id, asset_id, parent_id, lft, rgt,level, path, extension, title, alias, note, description, published, checked_out, checked_out_time, access, params, metadesc, metakey, metadata, created_user_id,created_time, modified_user_id, modified_time, hits,language)
SELECT 1, 0, 0, 0, 11, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{}', '', '', '', 0, '2009-10-18 16:07:09', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:26:37', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":"","foobar":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:27:35', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:27:57', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:28:15', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 6, 31, 1, 9, 10, 1, 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:28:33', 0, '1900-01-01 00:00:00', 0, '*';

SET IDENTITY_INSERT #__categories  OFF;


/****** Object:  Table [#__banners]    Script Date: 11/08/2010 18:41:22 ******/


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
	[checked_out_time] [datetime] NOT NULL,
	[publish_up] [datetime] NOT NULL,
	[publish_down] [datetime] NOT NULL,
	[reset] [datetime] NOT NULL,
	[created] [datetime] NOT NULL,
	[language] [nvarchar](7) NOT NULL,
 CONSTRAINT [PK_#__banners_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_banner_catid')
CREATE NONCLUSTERED INDEX [idx_banner_catid] ON [#__banners] 
(
	[catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_language')
CREATE NONCLUSTERED INDEX [idx_language] ON [#__banners] 
(
	[language] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_metakey_prefix')
CREATE NONCLUSTERED INDEX [idx_metakey_prefix] ON [#__banners] 
(
	[metakey_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_own_prefix')
CREATE NONCLUSTERED INDEX [idx_own_prefix] ON [#__banners] 
(
	[own_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banners]') AND name = N'idx_state')
CREATE NONCLUSTERED INDEX [idx_state] ON [#__banners] 
(
	[state] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__banners__cid__0F975522]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banners__cid__0F975522]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banners__cid__0F975522]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [cid]
END


End;

/****** Object:  Default [DF__#__banner__type__108B795B]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banner__type__108B795B]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banner__type__108B795B]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [type]
END


End;

/****** Object:  Default [DF__#__banner__name__117F9D94]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banner__name__117F9D94]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banner__name__117F9D94]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__banne__alias__1273C1CD]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__alias__1273C1CD]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__alias__1273C1CD]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [alias]
END


End;

/****** Object:  Default [DF__#__banne__impto__1367E606]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__impto__1367E606]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__impto__1367E606]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [imptotal]
END


End;

/****** Object:  Default [DF__#__banne__impma__145C0A3F]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__impma__145C0A3F]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__impma__145C0A3F]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [impmade]
END


End;

/****** Object:  Default [DF__#__banne__click__15502E78]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__click__15502E78]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__click__15502E78]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [clicks]
END


End;

/****** Object:  Default [DF__#__banne__click__164452B1]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__click__164452B1]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__click__164452B1]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [clickurl]
END


End;

/****** Object:  Default [DF__#__banne__state__173876EA]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__state__173876EA]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__state__173876EA]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__banne__catid__182C9B23]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__catid__182C9B23]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__catid__182C9B23]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [catid]
END


End;

/****** Object:  Default [DF__#__banne__stick__1920BF5C]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__stick__1920BF5C]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__stick__1920BF5C]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [sticky]
END


End;

/****** Object:  Default [DF__#__banne__order__1A14E395]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__order__1A14E395]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__order__1A14E395]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [ordering]
END


End;

/****** Object:  Default [DF__#__banne__own_p__1B0907CE]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__own_p__1B0907CE]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__own_p__1B0907CE]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [own_prefix]
END


End;

/****** Object:  Default [DF__#__banne__metak__1BFD2C07]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__metak__1BFD2C07]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__metak__1BFD2C07]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [metakey_prefix]
END


End;

/****** Object:  Default [DF__#__banne__purch__1CF15040]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__purch__1CF15040]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__purch__1CF15040]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((-1)) FOR [purchase_type]
END


End;

/****** Object:  Default [DF__#__banne__track__1DE57479]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__track__1DE57479]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__track__1DE57479]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((-1)) FOR [track_clicks]
END


End;

/****** Object:  Default [DF__#__banne__track__1ED998B2]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__track__1ED998B2]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__track__1ED998B2]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((-1)) FOR [track_impressions]
END


End;

/****** Object:  Default [DF__#__banne__check__1FCDBCEB]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__check__1FCDBCEB]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__check__1FCDBCEB]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__banne__check__20C1E124]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__check__20C1E124]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__check__20C1E124]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__banne__publi__21B6055D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__publi__21B6055D]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__publi__21B6055D]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_up]
END


End;

/****** Object:  Default [DF__#__banne__publi__22AA2996]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__publi__22AA2996]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__publi__22AA2996]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [publish_down]
END


End;

/****** Object:  Default [DF__#__banne__reset__239E4DCF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__reset__239E4DCF]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__reset__239E4DCF]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [reset]
END


End;

/****** Object:  Default [DF__#__banne__creat__24927208]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__creat__24927208]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__creat__24927208]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [created]
END


End;

/****** Object:  Default [DF__#__banne__langu__25869641]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__langu__25869641]') AND parent_object_id = OBJECT_ID(N'[#__banners]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__langu__25869641]') AND type = 'D')
BEGIN
ALTER TABLE [#__banners] ADD  DEFAULT (N'') FOR [language]
END


End;

/****** Object:  Table [#__banner_tracks]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__banner_tracks](
	[track_date] [datetime]  NOT NULL,
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND name = N'idx_banner_id')
CREATE NONCLUSTERED INDEX [idx_banner_id] ON [#__banner_tracks] 
(
	[banner_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND name = N'idx_track_date')
CREATE NONCLUSTERED INDEX [idx_track_date] ON [#__banner_tracks] 
(
	[track_date] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_tracks]') AND name = N'idx_track_type')
CREATE NONCLUSTERED INDEX [idx_track_type] ON [#__banner_tracks] 
(
	[track_type] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__banne__count__0DAF0CB0]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__count__0DAF0CB0]') AND parent_object_id = OBJECT_ID(N'[#__banner_tracks]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__count__0DAF0CB0]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_tracks] ADD  DEFAULT ((0)) FOR [count]
END


End;


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
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__user_notes]') AND name = N'idx_category_id')
CREATE NONCLUSTERED INDEX [idx_category_id] ON [#__user_notes] 
(
 [catid] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__user_notes]') AND name = N'idx_user_id')
CREATE NONCLUSTERED INDEX [idx_user_id] ON [#__user_notes] 
(
 [user_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF)
END;

/****** Object:  Default [DF__#__user___user___2610A626]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___user___2610A626]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___user___2610A626]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [user_id]
END

End;

/****** Object:  Default [DF__#__user___catid__2704CA5F]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___catid__2704CA5F]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___catid__2704CA5F]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [catid]
END

End;

/****** Object:  Default [DF__#__user___subje__27F8EE98]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___subje__27F8EE98]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___subje__27F8EE98]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (N'') FOR [subject]
END

End;

/****** Object:  Default [DF__#__user___state__28ED12D1]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___state__28ED12D1]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___state__28ED12D1]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [state]
END

End;

/****** Object:  Default [DF__#__user___check__29E1370A]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___check__29E1370A]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___check__29E1370A]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [checked_out]
END

End;

/****** Object:  Default [DF__#__user___check__2AD55B43]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___check__2AD55B43]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___check__2AD55B43]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [checked_out_time]
END

End;

/****** Object:  Default [DF__#__user___creat__2BC97F7C]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___creat__2BC97F7C]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___creat__2BC97F7C]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT ((0)) FOR [created_user_id]
END

End;

/****** Object:  Default [DF__#__user___creat__2CBDA3B5]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___creat__2CBDA3B5]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___creat__2CBDA3B5]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [created_time]
END

End;

/****** Object:  Default [DF__#__user___modif__2DB1C7EE]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___modif__2DB1C7EE]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___modif__2DB1C7EE]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [modified_time]
END

End;

/****** Object:  Default [DF__#__user___revie__2EA5EC27]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___revie__2EA5EC27]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___revie__2EA5EC27]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [review_time]
END

End;

/****** Object:  Default [DF__#__user___publi__2F9A1060]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___publi__2F9A1060]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___publi__2F9A1060]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [publish_up]
END

End;

/****** Object:  Default [DF__#__user___publi__308E3499]    Script Date: 12/30/2011 16:12:16 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__user___publi__308E3499]') AND parent_object_id = OBJECT_ID(N'[#__user_notes]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__user___publi__308E3499]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_notes] ADD  DEFAULT (getdate()) FOR [publish_down]
END

End;


SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__user_profiles]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__user_profiles](
 [user_id] [int] NOT NULL,
 [profile_key] [nvarchar](100) NOT NULL,
 [profile_value] [nvarchar](255) NOT NULL,
 [ordering] [int] NOT NULL,
 CONSTRAINT [#__user_profiles$idx_user_id_profile_key] UNIQUE CLUSTERED 
(
 [user_id] ASC,
 [profile_key] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;
/****** Object:  Default [DF__bzncw_use__order__40C49C62]    Script Date: 01/18/2012 15:57:10 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__use__order__40C49C62]') AND parent_object_id = OBJECT_ID(N'[#__user_profiles]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__use__order__40C49C62]') AND type = 'D')
BEGIN
ALTER TABLE [#__user_profiles] ADD  DEFAULT ((0)) FOR [ordering]
END

End;


/****** Object:  Table [#__banner_clients]    Script Date: 11/08/2010 18:41:22 ******/

SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__banner_clients]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__banner_clients](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[contact] [nvarchar](255) NOT NULL,
	[email] [nvarchar](255) NOT NULL,
	[extrainfo] [nvarchar](max) NOT NULL,
	[state] [smallint] NOT NULL,
	[checked_out] [bigint] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
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

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_clients]') AND name = N'idx_metakey_prefix')
CREATE NONCLUSTERED INDEX [idx_metakey_prefix] ON [#__banner_clients] 
(
	[metakey_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__banner_clients]') AND name = N'idx_own_prefix')
CREATE NONCLUSTERED INDEX [idx_own_prefix] ON [#__banner_clients] 
(
	[own_prefix] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

/****** Object:  Default [DF__#__banner__name__023D5A04]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banner__name__023D5A04]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banner__name__023D5A04]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [name]
END


End;

/****** Object:  Default [DF__#__banne__conta__03317E3D]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__conta__03317E3D]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__conta__03317E3D]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [contact]
END


End;

/****** Object:  Default [DF__#__banne__email__0425A276]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__email__0425A276]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__email__0425A276]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [email]
END


End;

/****** Object:  Default [DF__#__banne__state__0519C6AF]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__state__0519C6AF]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__state__0519C6AF]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((0)) FOR [state]
END


End;

/****** Object:  Default [DF__#__banne__check__060DEAE8]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__check__060DEAE8]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__check__060DEAE8]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((0)) FOR [checked_out]
END


End;

/****** Object:  Default [DF__#__banne__check__07020F21]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__check__07020F21]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__check__07020F21]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ('1900-01-01 00:00:00') FOR [checked_out_time]
END


End;

/****** Object:  Default [DF__#__banne__own_p__07F6335A]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__own_p__07F6335A]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__own_p__07F6335A]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((0)) FOR [own_prefix]
END


End;

/****** Object:  Default [DF__#__banne__metak__08EA5793]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__metak__08EA5793]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__metak__08EA5793]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT (N'') FOR [metakey_prefix]
END


End;

/****** Object:  Default [DF__#__banne__purch__09DE7BCC]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__purch__09DE7BCC]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__purch__09DE7BCC]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((-1)) FOR [purchase_type]
END


End;

/****** Object:  Default [DF__#__banne__track__0AD2A005]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__track__0AD2A005]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__track__0AD2A005]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((-1)) FOR [track_clicks]
END


End;

/****** Object:  Default [DF__#__banne__track__0BC6C43E]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__banne__track__0BC6C43E]') AND parent_object_id = OBJECT_ID(N'[#__banner_clients]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__banne__track__0BC6C43E]') AND type = 'D')
BEGIN
ALTER TABLE [#__banner_clients] ADD  DEFAULT ((-1)) FOR [track_impressions]
END


End;


/****** Object:  Table [#__assets]    Script Date: 11/08/2010 18:41:22 ******/


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
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__assets$idx_asset_name] UNIQUE NONCLUSTERED 
(
	[name] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__assets]') AND name = N'idx_lft_rgt')
CREATE NONCLUSTERED INDEX [idx_lft_rgt] ON [#__assets] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__assets]') AND name = N'idx_parent_id')
CREATE NONCLUSTERED INDEX [idx_parent_id] ON [#__assets] 
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__asset__paren__7E6CC920]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__asset__paren__7E6CC920]') AND parent_object_id = OBJECT_ID(N'[#__assets]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__asset__paren__7E6CC920]') AND type = 'D')
BEGIN
ALTER TABLE [#__assets] ADD  DEFAULT ((0)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__assets__lft__7F60ED59]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__assets__lft__7F60ED59]') AND parent_object_id = OBJECT_ID(N'[#__assets]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__assets__lft__7F60ED59]') AND type = 'D')
BEGIN
ALTER TABLE [#__assets] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__assets__rgt__00551192]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__assets__rgt__00551192]') AND parent_object_id = OBJECT_ID(N'[#__assets]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__assets__rgt__00551192]') AND type = 'D')
BEGIN
ALTER TABLE [#__assets] ADD  DEFAULT ((0)) FOR [rgt]
END


End;

ALTER TABLE [#__content] ADD tags nvarchar(MAX) NULL;

SET IDENTITY_INSERT #__assets  ON;

INSERT INTO #__assets (id, parent_id, lft, rgt, level, name, title, rules)
SELECT 1,0,1,414,0,'root.1','Root Asset','{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'
UNION ALL
SELECT 2,1,1,2,1,'com_admin','com_admin','{}'
UNION ALL
SELECT 3,1,3,6,1,'com_banners','com_banners','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION ALL
SELECT 4,1,7,8,1,'com_cache','com_cache','{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 5,1,9,10,1,'com_checkin','com_checkin','{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 6,1,11,12,1,'com_config','com_config','{}'
UNION ALL
SELECT 7,1,13,16,1,'com_contact','com_contact','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION ALL
SELECT 8,1,17,20,1,'com_content','com_content','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'
UNION ALL
SELECT 9,1,21,22,1,'com_cpanel','com_cpanel','{}'
UNION ALL
SELECT 10,1,23,24,1,'com_installer','com_installer','{"core.admin":{"7":1},"core.manage":{"7":1},"core.delete":[],"core.edit.state":[]}'
UNION ALL
SELECT 11,1,25,26,1,'com_languages','com_languages','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION ALL
SELECT 12,1,27,28,1,'com_login','com_login','{}'
UNION ALL
SELECT 13,1,29,30,1,'com_mailto','com_mailto','{}'
UNION ALL
SELECT 14,1,31,32,1,'com_massmail','com_massmail','{}'
UNION ALL
SELECT 15,1,33,34,1,'com_media','com_media','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'
UNION ALL
SELECT 16,1,35,36,1,'com_menus','com_menus','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION ALL
SELECT 17,1,37,38,1,'com_messages','com_messages','{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 18,1,39,40,1,'com_modules','com_modules','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION ALL
SELECT 19,1,41,44,1,'com_newsfeeds','com_newsfeeds','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION ALL
SELECT 20,1,45,46,1,'com_plugins','com_plugins','{"core.admin":{"7":1},"core.manage":[],"core.edit":[],"core.edit.state":[]}'
UNION ALL
SELECT 21,1,47,48,1,'com_redirect','com_redirect','{"core.admin":{"7":1},"core.manage":[]}'
UNION ALL
SELECT 22,1,49,50,1,'com_search','com_search','{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 23,1,51,52,1,'com_templates','com_templates','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION ALL
SELECT 24,1,53,54,1,'com_users','com_users','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.own":{"6":1},"core.edit.state":[]}'
UNION ALL
SELECT 25,1,55,58,1,'com_weblinks','com_weblinks','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'
UNION ALL
SELECT 26,1,59,60,1,'com_wrapper','com_wrapper','{}'
UNION ALL
SELECT 27, 8, 18, 19, 2, 'com_content.category.2', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION ALL
SELECT 28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION ALL
SELECT 29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION ALL
SELECT 30, 19, 42, 43, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION ALL
SELECT 31, 25, 56, 57, 2, 'com_weblinks.category.6', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}';

SET IDENTITY_INSERT #__assets  OFF;


/****** Object:  Table [#__usergroups]    Script Date: 11/08/2010 18:41:22 ******/


SET QUOTED_IDENTIFIER ON;

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__usergroups](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[parent_id] [bigint] NOT NULL,
	[lft] [bigint] NOT NULL,
	[rgt] [bigint] NOT NULL,
	[title] [nvarchar](255) NOT NULL,
 CONSTRAINT [PK_#__usergroups_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF),
 CONSTRAINT [#__usergroups$idx_usergroup_parent_title_lookup] UNIQUE NONCLUSTERED 
(
	[title] ASC,
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND name = N'idx_usergroup_title_lookup')
CREATE NONCLUSTERED INDEX [idx_usergroup_title_lookup] ON [#__usergroups] 
(
	[title] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND name = N'idx_usergroup_adjacency_lookup')
CREATE NONCLUSTERED INDEX [idx_usergroup_adjacency_lookup] ON [#__usergroups] 
(
	[parent_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[#__usergroups]') AND name = N'idx_usergroup_nested_set_lookup')
CREATE NONCLUSTERED INDEX [idx_usergroup_nested_set_lookup] ON [#__usergroups] 
(
	[lft] ASC,
	[rgt] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);


/****** Object:  Default [DF__#__userg__paren__72910220]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__userg__paren__72910220]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__userg__paren__72910220]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT ((0)) FOR [parent_id]
END


End;

/****** Object:  Default [DF__#__usergro__lft__73852659]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__usergro__lft__73852659]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__usergro__lft__73852659]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT ((0)) FOR [lft]
END


End;

/****** Object:  Default [DF__#__usergro__rgt__74794A92]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__usergro__rgt__74794A92]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__usergro__rgt__74794A92]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT ((0)) FOR [rgt]
END


End;

/****** Object:  Default [DF__#__userg__title__756D6ECB]    Script Date: 11/08/2010 18:41:22 ******/
IF Not EXISTS (SELECT * FROM sys.default_constraints WHERE object_id = OBJECT_ID(N'[DF__#__userg__title__756D6ECB]') AND parent_object_id = OBJECT_ID(N'[#__usergroups]'))
Begin
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__#__userg__title__756D6ECB]') AND type = 'D')
BEGIN
ALTER TABLE [#__usergroups] ADD  DEFAULT (N'') FOR [title]
END


End;


SET IDENTITY_INSERT #__usergroups  ON;
INSERT INTO #__usergroups (id ,parent_id ,lft ,rgt ,title)
SELECT 1, 0, 1, 20, 'Public'
UNION ALL
SELECT 2, 1, 6, 17, 'Registered'
UNION ALL
SELECT 3, 2, 7, 14, 'Author'
UNION ALL
SELECT 4, 3, 8, 11, 'Editor'
UNION ALL
SELECT 5, 4, 9, 10, 'Publisher'
UNION ALL
SELECT 6, 1, 2, 5, 'Manager'
UNION ALL
SELECT 7, 6, 3, 4, 'Administrator'
UNION ALL
SELECT 8, 1, 18, 19, 'Super Users';

SET IDENTITY_INSERT #__usergroups  OFF;







