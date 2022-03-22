ALTER TABLE `#__menu_types` ADD COLUMN `client_id` int NOT NULL DEFAULT 0;

UPDATE `#__menu` SET `published` = 1 WHERE `menutype` = 'main' OR `menutype` = 'menu';
