-- Sync menutype for admin menu and set client_id correct
UPDATE [#__menu] SET [client_id] = 1 WHERE [menutype] = 'main' OR [menutype] = 'menu';
UPDATE [#__menu] SET [menutype] = 'main' WHERE [menutype] = 'main' OR [menutype] = 'menu';
