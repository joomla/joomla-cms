-- Enable all the protected plugin extensions so they don't eternally disabled.
UPDATE [#__extensions] SET [enabled] = 1 WHERE [type] = 'plugin' AND [protected] = 1;
