INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'com_mails', 'component', 'com_mails', '', 1, 1, 1, 1, '', '', 0, '1970-01-01 00:00:00', 0, 0);

CREATE TABLE IF NOT EXISTS "#__mail_templates" (
  "mail_id" varchar(127) NOT NULL DEFAULT '',
  "language" char(7) NOT NULL DEFAULT '',
  "subject" varchar(255) NOT NULL DEFAULT '',
  "body" TEXT NOT NULL,
  "htmlbody" TEXT NOT NULL,
  "attachments" TEXT NOT NULL,
  "params" TEXT NOT NULL,
  CONSTRAINT "#__mail_templates_idx_mail_id_language" UNIQUE ("mail_id", "language"),
);
CREATE INDEX "#__mail_templates_idx_mail_id" ON "#__mail_templates" ("mail_id");
CREATE INDEX "#__mail_templates_idx_language" ON "#__mail_templates" ("language");

INSERT INTO "#__mail_templates" ("mail_id", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES ('com_config.test_mail', '', 'COM_CONFIG_SENDMAIL_SUBJECT', 'COM_CONFIG_SENDMAIL_BODY', '', '', '{"tags":["sitename","method"]}');
