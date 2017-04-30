-- Replace menutype = 'main' is reserved for backend so replace it
UPDATE `#__menu` SET `#__menu`.`menutype` = 'menutype_main_is_reserved', `client_id` = 1 WHERE `#__menu`.`menutype` = 'main';
UPDATE `#__menu_types` SET `#__menu_types`.`menutype` = 'menutype_main_is_reserved', `client_id` = 1 WHERE  `#__menu_types`.`menutype` = 'main';
