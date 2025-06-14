INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
('plg_task_deleteactionlogs', 'plugin', 'deleteactionlogs', 'task', 0, 1, 1, 0, 1, '', '{}', '', NULL, NULL, 0, 0),
('plg_task_privacyconsent', 'plugin', 'privacyconsent', 'task', 0, 1, 1, 0, 1, '', '{}', '', NULL, NULL, 0, 0),
('plg_task_rotatelogs', 'plugin', 'rotatelogs', 'task', 0, 1, 1, 0, 1, '', '{}', '', NULL, NULL, 0, 0),
('plg_task_sessiongc', 'plugin', 'sessiongc', 'task', 0, 1, 1, 0, 1, '', '{}', '', NULL, NULL, 0, 0),
('plg_task_updatenotification', 'plugin', 'updatenotification', 'task', 0, 1, 1, 0, 1, '', '{}', '', NULL, NULL, 0, 0);

INSERT INTO "#__mail_templates" ("template_id", "extension", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES
('plg_task_privacyconsent.request.reminder', 'plg_task_privacyconsent', '', 'PLG_TASK_PRIVACYCONSENT_EMAIL_REMIND_SUBJECT', 'PLG_TASK_PRIVACYCONSENT_EMAIL_REMIND_BODY', '', '', '{"tags":["sitename","url","tokenurl","formurl","token"]}'),
('plg_task_updatenotification.mail', 'plg_task_updatenotification', '', 'PLG_TASK_UPDATENOTIFICATION_EMAIL_SUBJECT', 'PLG_TASK_UPDATENOTIFICATION_EMAIL_BODY', '', '', '{"tags":["newversion","curversion","sitename","url","link","releasenews"]}');

DELETE FROM "#__mail_templates" WHERE "template_id" IN ('plg_system_privacyconsent.request.reminder', 'plg_system_updatenotification.mail');

DELETE FROM "#__postinstall_messages" WHERE "condition_file" = 'site://plugins/system/updatenotification/postinstall/updatecachetime.php';
