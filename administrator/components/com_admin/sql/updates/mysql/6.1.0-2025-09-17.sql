CREATE TABLE IF NOT EXISTS `#__magiclogin_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  `created` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
('plg_system_magiclogin.magiclink', 'plg_system_magiclogin', '', 'PLG_SYSTEM_MAGICLOGIN_EMAIL_SUBJECT', 'PLG_SYSTEM_MAGICLOGIN_EMAIL_BODY', 'PLG_SYSTEM_MAGICLOGIN_EMAIL_HTMLBODY', '', '{"tags":["sitename","username","magic_link","expiry_minutes"]}');