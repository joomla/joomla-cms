UPDATE `#__extensions`
   SET `params` = '{}'
 WHERE `type`= 'plugin' AND `element` = 'tasknotification' AND `folder` = 'system' AND `client_id` = 0 AND `params` = '';

UPDATE `#__mail_templates`
   SET `params` = '{"tags": ["task_id", "task_title"]}'
 WHERE `template_id`= 'plg_system_tasknotification.orphan_mail' AND `params` = '{"tags": ["task_id", "task_title", ""]}';
