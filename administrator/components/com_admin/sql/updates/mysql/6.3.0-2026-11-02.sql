INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
('com_users.new_credentials', 'com_users', '', 'COM_USERS_EMAIL_NEW_CREDENTIALS_SUBJECT', 'COM_USERS_EMAIL_NEW_CREDENTIALS_BODY', '', '', '{"tags":["name","sitename","url","username","password","email"]}');
