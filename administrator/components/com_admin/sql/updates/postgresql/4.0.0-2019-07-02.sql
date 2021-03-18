CREATE TABLE IF NOT EXISTS "#__webauthn_credentials" (
    "id"         varchar(1000)    NOT NULL,
    "user_id"    varchar(128)     NOT NULL,
    "label"      varchar(190)     NOT NULL,
    "credential" TEXT             NOT NULL,
    PRIMARY KEY ("id")
);

CREATE INDEX "#__webauthn_credentials_user_id" ON "#__webauthn_credentials" ("user_id");

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_system_webauthn', 'plugin', 'webauthn', 'system', 0, 1, 1, 0, '', '{}', 0, '1970-01-01 00:00:00', 8, 0);
