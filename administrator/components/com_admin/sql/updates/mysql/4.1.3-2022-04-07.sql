UPDATE `#__mail_templates`
   SET `params` = '{"tags":["message","date","extension","username"]}'
 WHERE `template_id` = 'com_actionlogs.notification' AND `params` = '{"tags":["message","date","extension"]}';

UPDATE `#__mail_templates`
   SET `params` = '{"tags":["sitename","name","email","subject","body","url","customfields","contactname"]}'
 WHERE `template_id` = 'com_contact.mail.copy' AND `params` = '{"tags":["sitename","name","email","subject","body","url","customfields"]}';
