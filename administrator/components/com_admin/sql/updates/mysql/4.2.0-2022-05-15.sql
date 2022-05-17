--
-- Create the new table for captive TFA
--
CREATE TABLE IF NOT EXISTS `#__user_tfa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `method` varchar(100) NOT NULL DEFAULT '',
  `default` tinyint NOT NULL DEFAULT 0,
  `options` longtext NOT NULL,
  `created_on` datetime NOT NULL,
  `last_used` datetime,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci COMMENT='Two Factor Authentication settings';

--
-- Remove obsolete postinstallation message
--
DELETE FROM `#__postinstall_messages` WHERE `condition_file` = 'site://plugins/twofactorauth/totp/postinstall/actions.php';

--
-- Add new captive TFA plugins
--
INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`) VALUES
(0, 'plg_twofactorauth_fixed', 'plugin', 'fixed', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_twofactorauth_webauthn', 'plugin', 'webauthn', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_twofactorauth_email', 'plugin', 'email', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 4, 0);

--
-- Add post-installation message
--
INSERT IGNORE INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description_key`, `action_key`, `language_extension`, `language_client_id`, `type`, `action_file`, `action`, `condition_file`, `condition_method`, `version_introduced`, `enabled`)
SELECT `extension_id`, 'COM_USERS_POSTINSTALL_TWOFACTORAUTH_TITLE', 'COM_USERS_POSTINSTALL_TWOFACTORAUTH_BODY', 'COM_USERS_POSTINSTALL_TWOFACTORAUTH_ACTION', 'com_users', 1, 'action', 'admin://components/com_users/postinstall/twofactorauth.php', 'com_users_postinstall_action', 'admin://components/com_users/postinstall/twofactorauth.php', 'com_users_postinstall_condition', '4.2.0', 1 FROM `#__extensions` WHERE `name` = 'files_joomla';

--
-- Create a mail template for plg_twofactorauth_email
--
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
('plg_twofactorauth_email.mail', 'plg_twofactorauth_email', '', 'PLG_TWOFACTORAUTH_EMAIL_EMAIL_SUBJECT', 'PLG_TWOFACTORAUTH_EMAIL_EMAIL_BODY', '', '', '{"tags":["code","sitename","siteurl","username","email","fullname"]}');
