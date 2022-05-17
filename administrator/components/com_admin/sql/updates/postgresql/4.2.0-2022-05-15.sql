--
-- Create the new table for captive TFA
--
CREATE TABLE IF NOT EXISTS "#__user_tfa" (
  "id" serial NOT NULL,
  "user_id" bigint NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "method" varchar(100) DEFAULT '' NOT NULL,
  "default" smallint DEFAULT 0 NOT NULL,
  "options" text NOT NULL,
  "created_on" timestamp without time zone NOT NULL,
  "last_used" timestamp without time zone,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__user_tfa_idx_user_id" ON "#__user_tfa" ("user_id") /** CAN FAIL **/;

COMMENT ON TABLE "#__user_tfa" IS 'Two Factor Authentication settings';

--
-- Remove obsolete postinstallation message
--
DELETE FROM "#__postinstall_messages" WHERE "condition_file" = 'site://plugins/twofactorauth/totp/postinstall/actions.php';

--
-- Add new captive TFA plugins
--
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_twofactorauth_fixed', 'plugin', 'fixed', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_twofactorauth_webauthn', 'plugin', 'webauthn', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_twofactorauth_email', 'plugin', 'email', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 4, 0);

--
-- Add post-installation message
--
INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "action_key", "language_extension", "language_client_id", "type", "action_file", "action", "condition_file", "condition_method", "version_introduced", "enabled")
SELECT "extension_id", 'COM_USERS_POSTINSTALL_TWOFACTORAUTH_TITLE', 'COM_USERS_POSTINSTALL_TWOFACTORAUTH_BODY', 'COM_USERS_POSTINSTALL_TWOFACTORAUTH_ACTION', 'com_users', 1, 'action', 'admin://components/com_users/postinstall/twofactorauth.php', 'com_users_postinstall_action', 'admin://components/com_users/postinstall/twofactorauth.php', 'com_users_postinstall_condition', '4.2.0', 1 FROM "#__extensions" WHERE "name" = 'files_joomla'
ON CONFLICT DO NOTHING;

--
-- Create a mail template for plg_twofactorauth_email
--
INSERT INTO "#__mail_templates" ("template_id", "extension", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES
('plg_twofactorauth_email.mail', 'plg_twofactorauth_email', '', 'PLG_TWOFACTORAUTH_EMAIL_EMAIL_SUBJECT', 'PLG_TWOFACTORAUTH_EMAIL_EMAIL_BODY', '', '', '{"tags":["code","sitename","siteurl","username","email","fullname"]}')
ON CONFLICT DO NOTHING;
