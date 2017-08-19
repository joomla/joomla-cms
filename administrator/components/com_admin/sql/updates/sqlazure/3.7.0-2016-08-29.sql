/****** Object:  Table [#__fields] ******/

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

/****** Object:  Table [#__fields_values] ******/

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

SET IDENTITY_INSERT [#__extensions] ON;

INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 33, 'com_fields', 'component', 'com_fields', '', 1, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 461, 'plg_system_fields', 'plugin', 'fields', 'system', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0


SET IDENTITY_INSERT [#__extensions] OFF;
