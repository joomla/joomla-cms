IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__wf_profiles]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__wf_profiles](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](250) NOT NULL,
	[description] [text] NOT NULL,
	[users] [text] NOT NULL,
	[types] [text] NOT NULL,
	[components] [nvarchar](max) NOT NULL,
	[area] [smallint] NOT NULL,
    [device] [nvarchar](250) NOT NULL,
	[rows] [nvarchar](max) NOT NULL,
	[plugins] [nvarchar](max) NOT NULL,
	[published] [smallint] NOT NULL,
	[ordering] [int] NOT NULL,
	[checked_out] [int] NOT NULL,
	[checked_out_time] [datetime] NOT NULL,
	[params] [nvarchar](max) NOT NULL,
 CONSTRAINT [PK_#__wf_profiles_id] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;