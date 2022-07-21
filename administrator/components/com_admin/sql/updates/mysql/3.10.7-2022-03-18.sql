ALTER TABLE `#__users` ADD COLUMN `authProvider` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Name of used authentication plugin';
