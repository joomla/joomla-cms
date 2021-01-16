CREATE TABLE "#__action_logs_users" (
  "user_id" int NOT NULL,
  "notify" tinyint NOT NULL,
  "extensions" nvarchar(max) NOT NULL,
 CONSTRAINT "PK_#__action_logs_users_user_id" PRIMARY KEY NONCLUSTERED
(
  "user_id" ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];

CREATE CLUSTERED INDEX "idx_notify" ON "#__action_logs_users"
(
  "notify" ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
