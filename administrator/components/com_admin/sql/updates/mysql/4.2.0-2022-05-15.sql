--
-- Create the new table for captive TFA
--
CREATE TABLE IF NOT EXISTS `#__user_tfa` (
  `id`         SERIAL,
  `user_id`    int unsigned NOT NULL,
  `title`      VARCHAR(255)    NOT NULL,
  `method`     VARCHAR(100)    NOT NULL,
  `default`    TINYINT(1)      NOT NULL DEFAULT 0,
  `options`    LONGTEXT        NULL,
  `created_on` DATETIME        NULL,
  `last_used`  DATETIME        NULL,
  INDEX `#__user_tfa_user` (`user_id`)
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
(0, 'plg_twofactorauth_webauthn', 'plugin', 'webauthn', 'twofactorauth', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_twofactorauth_email', 'plugin', 'email', 'twofactorauth', 0, 0, 1, 0, 1, '', '', '', 4, 0);
