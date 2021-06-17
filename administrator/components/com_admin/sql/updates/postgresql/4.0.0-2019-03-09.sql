-- From 4.0.0-2018-09-12.sql
UPDATE "#__extensions" SET "client_id" = 1 WHERE "name" = 'com_mailto';
UPDATE "#__extensions" SET "client_id" = 1 WHERE "name" = 'com_wrapper';

-- From 4.0.0-2018-10-18.sql
UPDATE "#__content_types" SET "router" = '' WHERE "type_alias" = 'com_users.user';

-- From 4.0.0-2019-01-05.sql
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_api-authentication_basic', 'plugin', 'basic', 'api-authentication', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0),
(0, 'plg_webservices_content', 'plugin', 'content', 'webservices', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

-- From 4.0.0-2019-01-16.sql
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('com_mails', 'component', 'com_mails', '', 1, 1, 1, 1, '{"name":"com_mails","type":"component","creationDate":"January 2019","author":"Joomla! Project","copyright":"(C) 2019 Open Source Matters, Inc. <https://www.joomla.org>","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"4.0.0","description":"COM_MAILS_XML_DESCRIPTION","group":""}', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);

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

-- From 4.0.0-2019-02-03.sql
DELETE FROM "#__menu" WHERE "link" = 'index.php?option=com_postinstall' AND "menutype" = 'main';
DELETE FROM "#__menu" WHERE "link" = 'index.php?option=com_redirect' AND "menutype" = 'main';
DELETE FROM "#__menu" WHERE "link" = 'index.php?option=com_joomlaupdate' AND "menutype" = 'main';

-- From 4.0.0-2019-03-09.sql
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_system_skipto', 'plugin', 'skipto', 'system', 0, 1, 1, 0, '', '{}', '', 0, '1970-01-01 00:00:00', 0, 0);
