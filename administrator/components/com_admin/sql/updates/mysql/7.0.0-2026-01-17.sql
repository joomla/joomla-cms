-- uninstall previous what's new tours
DELETE FROM `#__mail_templates`
 WHERE `template_id` = 'com_contact.mail.copy';
