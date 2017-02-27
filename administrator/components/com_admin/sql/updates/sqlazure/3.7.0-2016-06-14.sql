CREATE TABLE [#__content_draft] (
  [id]         [bigint] IDENTITY(1,1) NOT NULL,
  [articleId]  [int] NOT NULL,
  [created]    [datetime]      NOT NULL,
  [sharetoken] [nvarchar](32)  NOT NULL,
  [shareurl]   [nvarchar](255) NOT NULL,
  CONSTRAINT [PK_#__id] PRIMARY KEY CLUSTERED
    (
      [id] ASC
    )
) ON [PRIMARY];