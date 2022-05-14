--
-- Create the new table for captive TFA
--
CREATE TABLE IF NOT EXISTS "#__user_tfa" (
  "id" serial NOT NULL,
  "user_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "method" varchar(100) DEFAULT '' NOT NULL,
  "default" smallint DEFAULT 0,
  "options" text NOT NULL,
  "created_on" timestamp without time zone NOT NULL,
  "last_used" timestamp without time zone NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__user_tfa_idx_user_id" ON "#__user_tfa" ("user_id");

COMMENT ON TABLE "#__user_tfa" IS 'Two Factor Authentication settings';

--
-- Remove obsolete postinstallation message
--
DELETE FROM "#__postinstall_messages" WHERE "condition_file" = 'site://plugins/twofactorauth/totp/postinstall/actions.php';

--
-- Add new captive TFA plugins
--
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_system_tfa', 'plugin', 'tfa', 'system', 0, 1, 1, 0, 1, '', '', '', 24, 0),
(0, 'plg_twofactorauth_fixed', 'plugin', 'fixed', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_twofactorauth_webauthn', 'plugin', 'webauthn', 'twofactorauth', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_twofactorauth_email', 'plugin', 'email', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 4, 0);
