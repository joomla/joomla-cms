CREATE TABLE IF NOT EXISTS "#__webauthn_credentials" (
    "id"         varchar(1000)    NOT NULL,
    "user_id"    varchar(128)     NOT NULL,
    "label"      varchar(190)     NOT NULL,
    "credential" TEXT             NOT NULL,
    PRIMARY KEY ("id")
);

CREATE INDEX "#__webauthn_credentials_user_id" ON "#__webauthn_credentials" ("user_id");

