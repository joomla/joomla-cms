/****** Object:  Table [#__user_keys] ******/
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [#__user_keys] (
  [id] int(10) [int] IDENTITY(1,1) NOT NULL,
  [user_id] [nvarchar](255) NOT NULL DEFAULT '',
  [token] [nvarchar](255) NOT NULL DEFAULT '',
  [series] [nvarchar](255) NOT NULL DEFAULT '',
  [invalid] [tinyint] NOT NULL,
  [time] [nvarchar](255) NOT NULL DEFAULT '',
  [uastring] [nvarchar](255) NOT NULL DEFAULT '',
  CONSTRAINT [PK_#__user_keys_id] PRIMARY KEY CLUSTERED
    (
      [id] ASC
    )WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY];
CREATE NONCLUSTERED INDEX [idx_series] ON [#__user_keys]
(
  [series] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

 
