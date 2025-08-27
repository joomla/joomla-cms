--
-- Add a template tag for ip_address on Joomla update.
-- New installs will have the default value set in the installation support sql.
--

UPDATE `#__mail_templates`
SET `params` = '{"tags":["messages","message","date","extension","username","ip_address"]}'
WHERE `template_id` = 'com_actionlogs.notification';
