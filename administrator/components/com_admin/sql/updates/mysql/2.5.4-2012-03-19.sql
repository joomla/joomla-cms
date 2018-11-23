ALTER TABLE `#__languages` ADD COLUMN `access` integer unsigned NOT NULL default 0 AFTER `published`;

ALTER TABLE `#__languages` ADD INDEX `idx_access` (`access`);

UPDATE `#__categories` SET `extension` = 'com_users.notes' WHERE `extension` = 'com_users';

UPDATE `#__extensions` SET `enabled` = '1' WHERE `protected` = '1' AND `type` <> 'plugin';
