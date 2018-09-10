SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(35, 0, 'com_privacy', 'component', 'com_privacy', '', 1, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0);

SET IDENTITY_INSERT #__extensions  OFF;

CREATE TABLE "#__privacy_requests" (
  "id" int IDENTITY(1,1) NOT NULL,
  "email" nvarchar(100) NOT NULL DEFAULT '',
  "requested_at" datetime2(0) NOT NULL DEFAULT '1900-01-01 00:00:00',
  "status" smallint NOT NULL,
  "request_type" nvarchar(25) NOT NULL DEFAULT '',
  "confirm_token" nvarchar(100) NOT NULL DEFAULT '',
  "confirm_token_created_at" datetime2(0) NOT NULL DEFAULT '1900-01-01 00:00:00',
  "checked_out" bigint NOT NULL DEFAULT 0,
  "checked_out_time" datetime2(0) NOT NULL DEFAULT '1900-01-01 00:00:00',
CONSTRAINT "PK_#__privacy_requests_id" PRIMARY KEY CLUSTERED(
  "id" ASC)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]) ON [PRIMARY];

CREATE NONCLUSTERED INDEX "idx_checkout" ON "#__privacy_requests" (
  "checked_out" ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
