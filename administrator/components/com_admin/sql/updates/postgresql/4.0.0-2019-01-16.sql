INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "checked_out", "checked_out_time", "ordering", "state") VALUES
('com_mails', 'component', 'com_mails', '', 1, 1, 1, 1, '{"name":"com_mails","type":"component","creationDate":"January 2019","author":"Joomla! Project","copyright":"(C) 2005 - 2020 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"4.0.0","description":"COM_MAILS_XML_DESCRIPTION","group":""}', '{}', 0, '1970-01-01 00:00:00', 0, 0);

CREATE TABLE IF NOT EXISTS "#__mail_templates" (
  "template_id" varchar(127) NOT NULL DEFAULT '',
  "language" char(7) NOT NULL DEFAULT '',
  "subject" varchar(255) NOT NULL DEFAULT '',
  "body" TEXT NOT NULL,
  "htmlbody" TEXT NOT NULL,
  "attachments" TEXT NOT NULL,
  "params" TEXT NOT NULL,
  CONSTRAINT "#__mail_templates_idx_template_id_language" UNIQUE ("template_id", "language")
);
CREATE INDEX "#__mail_templates_idx_template_id" ON "#__mail_templates" ("template_id");
CREATE INDEX "#__mail_templates_idx_language" ON "#__mail_templates" ("language");

INSERT INTO "#__mail_templates" ("template_id", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES ('com_config.test_mail', '', 'COM_CONFIG_SENDMAIL_SUBJECT', 'COM_CONFIG_SENDMAIL_BODY', '', '', '{"tags":["sitename","method"]}');
