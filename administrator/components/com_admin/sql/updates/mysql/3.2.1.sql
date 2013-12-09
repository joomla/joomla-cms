DELETE FROM `#__postinstall_messages` WHERE `title_key` = 'PLG_USER_JOOMLA_POSTINSTALL_STRONGPW_TITLE';
DROP TABLE `#__user_keys`;
ALTER TABLE `#__users` ADD COLUMN `rememberme` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Remember Me Key for authentication' AFTER `resetCount`;

