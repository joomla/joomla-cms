-- remove old email parameter from scheduled tasks
UPDATE "#__scheduler_task" 
SET "params" = jsonb_set("params", '{email}', '""') 
WHERE "params" ? 'email';

-- add post-installation message for task update notification. 
--
-- See https://github.com/joomla/joomla-cms/pull/43182 for details.
--
INSERT INTO "#__postinstall_messages" 
("extension_id", 
"title_key", 
"description_key", 
"action_key",
"language_extension", 
"language_client_id", 
"type",
"action_file", 
"action", 
"condition_file",
"condition_method",
"version_introduced",
"enabled")
SELECT 
"extension_id", 
'PLG_TASK_UPDATENOTIFICATION_POSTINSTALL_TITLE', 
'PLG_TASK_UPDATENOTIFICATION_POSTINSTALL_DESCRIPTION',
'', 
'plg_task_updatenotification', 
1,
'message',
'',
'',
'admin://plugins/task/updatenotification/postinstall/updatenotification.php',
'',
'6.1.0',
1
FROM "#__extensions" WHERE "name" = 'files_joomla';