SET IDENTITY_INSERT "#__extensions" ON;

INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(485, 0, 'plg_system_privacyconsent', 'plugin', 'privacyconsent', 'system', 0, 0, 1, 0, '', '{}', '', '', 0, '1900-01-01 00:00:00', 0, 0);

SET IDENTITY_INSERT "#__extensions" OFF;

--
-- Table structure for table `#__privacy_consents`
--

CREATE TABLE "#__privacy_consents" (
  "id" int IDENTITY(1,1) NOT NULL,
  "user_id" bigint NOT NULL DEFAULT 0,
  "created" datetime2(0) NOT NULL DEFAULT '1900-01-01 00:00:00',
  "subject" nvarchar(255) NOT NULL DEFAULT '',
  "body" nvarchar(max) NOT NULL,
  "remind" smallint NOT NULL,
  "token" nvarchar(100) NOT NULL DEFAULT '',
CONSTRAINT "PK_#__privacy_consents_id" PRIMARY KEY CLUSTERED(
  "id" ASC)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]) ON [PRIMARY];

CREATE NONCLUSTERED INDEX "idx_user_id" ON "#__privacy_consents" (
  "user_id" ASC)
WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);
