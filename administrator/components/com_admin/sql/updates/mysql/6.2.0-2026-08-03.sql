--
-- Add the mail template for the extensions update notification task
--

INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
('plg_task_updatenotification.extensions', 'plg_task_updatenotification', '', 'PLG_TASK_UPDATENOTIFICATION_EXTENSIONS_EMAIL_SUBJECT', 'PLG_TASK_UPDATENOTIFICATION_EXTENSIONS_EMAIL_BODY', '', '', '{"tags":["count","extensions","sitename","url","link"]}');
