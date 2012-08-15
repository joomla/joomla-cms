ALTER TABLE "#__languages" ADD COLUMN "access" integer NOT NULL DEFAULT 0;

CREATE INDEX "#__languages_idx_access" ON "#__languages" ("access");

UPDATE "#__categories" SET "extension"='com_users.notes' WHERE "extension" = 'com_users';

UPDATE "#__extensions" SET "enabled"=1 WHERE "protected" = 1 AND "type" <> 'plugin';