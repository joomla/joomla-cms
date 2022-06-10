ALTER TABLE `#__users` ADD COLUMN `requireReset` tinyint NOT NULL DEFAULT 0 COMMENT 'Require user to reset password on next login' AFTER `otep`;
