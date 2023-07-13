ALTER TABLE `#__menu_types` ADD COLUMN `ordering` int NOT NULL AUTO_INCREMENT AFTER `client_id`;
UPDATE `#__menu_types` SET `ordering` = `id` WHERE `client_id` = 0;
