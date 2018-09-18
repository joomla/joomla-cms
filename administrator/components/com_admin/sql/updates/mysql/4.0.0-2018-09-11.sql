CREATE TABLE IF NOT EXISTS `#__mail_templates` (
  `mail_id` VARCHAR(127) NOT NULL DEFAULT '',
  `language` char(7) NOT NULL DEFAULT '',
  `subject` VARCHAR(255) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `htmlbody` TEXT NOT NULL,
  `attachments` TEXT NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY (`mail_id`, `language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__mail_templates` (`mail_id`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES ('com_config.test_mail', '', 'COM_CONFIG_SENDMAIL_SUBJECT', 'COM_CONFIG_SENDMAIL_BODY', '', '', '{"tags":["sitename","method"]}');
