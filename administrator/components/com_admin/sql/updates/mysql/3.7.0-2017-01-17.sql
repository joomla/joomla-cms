-- Replace menutype = 'main' is reserved for backend so replace it
UPDATE `#__menu` SET `menutype` = 'menutype_main_is_reserved', `client_id` = 1 WHERE `menutype` = 'main';
UPDATE `#__menu_types` SET `menutype` = 'menutype_main_is_reserved', `client_id` = 1 WHERE `menutype` = 'main';
