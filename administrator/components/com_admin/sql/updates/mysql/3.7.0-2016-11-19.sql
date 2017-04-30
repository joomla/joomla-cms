ALTER TABLE `#__menu_types` ADD COLUMN `client_id` int(11) NOT NULL DEFAULT 0 AFTER `description`;
UPDATE `#__menu` SET `published` = 1 WHERE `menutype` = 'main' OR `menutype` = 'menu';
