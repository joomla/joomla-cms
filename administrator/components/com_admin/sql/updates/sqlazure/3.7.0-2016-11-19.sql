ALTER TABLE [#__menu_types] ADD [client_id] [tinyint] NOT NULL DEFAULT 0  AFTER [description];
UPDATE [#__menu] SET [published] = 1 WHERE [menutype] = 'main' OR [menutype] = 'menu';
