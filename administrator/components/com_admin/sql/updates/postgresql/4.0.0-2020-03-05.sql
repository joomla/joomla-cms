CREATE TABLE IF NOT EXISTS "#__webauthn_credentials"
(
  "id" varchar(1000) NOT NULL,
  "user_id" varchar(128) NOT NULL,
  "label" varchar(190) NOT NULL,
  "credential" text NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__webauthn_credentials_user_id" ON "#__webauthn_credentials" ("user_id");

COMMENT ON COLUMN "#__webauthn_credentials"."id" IS 'Credential ID';
COMMENT ON COLUMN "#__webauthn_credentials"."user_id" IS 'User handle';
COMMENT ON COLUMN "#__webauthn_credentials"."label" IS 'Human readable label';
COMMENT ON COLUMN "#__webauthn_credentials"."credential" IS 'Credential source data, JSON format';

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_system_webauthn', 'plugin', 'webauthn', 'system', 0, 1, 1, 0, '', '{}', 0, NULL, 8, 0);
