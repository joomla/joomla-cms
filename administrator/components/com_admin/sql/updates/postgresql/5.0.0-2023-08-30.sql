INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('plg_task_privacyconsent', 'plugin', 'privacyconsent', 'task', 0, 1, 1, 0, 1, '', '{}', '', NULL, NULL, 0, 0);
INSERT INTO "#__mail_templates" ("template_id", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES
('plg_task_privacyconsent.request.reminder', '', 'PLG_TASK_PRIVACYCONSENT_EMAIL_REMIND_SUBJECT', 'PLG_TASK_PRIVACYCONSENT_EMAIL_REMIND_BODY', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}');
DELETE FROM "#__mail_templates" WHERE "template_id" = 'plg_system_privacyconsent.request.reminder';