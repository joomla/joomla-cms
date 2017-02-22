-- Sync menutype for admin menu and set client_id correct
UPDATE `#__menu` SET `menutype` = 'main', `client_id` = 1  WHERE `menutype` = 'main' OR `menutype` = 'menu';
