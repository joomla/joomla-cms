UPDATE `#__mail_templates`
SET `params` = '{"tags":["messages","message","date","extension","username"]}'
WHERE `template_id` = 'com_actionlogs.notification';
