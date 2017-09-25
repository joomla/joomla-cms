-- Sync menutype for admin menu and set client_id correct
-- Note: This change had to be modified with Joomla 3.7.3 because the
-- original version made site menus disappear if there were menu types
-- "main" or "menu" defined for the site.

-- Step 1: If there is any user-defined menu and menu type "main" for the site
-- (client_id = 0), then change the menu type for the menu, any module and the
-- menu type to something very likely not being used yet and just within the
-- max. length of 24 characters.
UPDATE [#__menu]
   SET [menutype] = 'main_is_reserved_133C585'
 WHERE [client_id] = 0
   AND [menutype] = 'main'
   AND (SELECT COUNT([id]) FROM [#__menu_types] WHERE [client_id] = 0 AND [menutype] = 'main') > 0;

UPDATE [#__modules]
   SET [params] = REPLACE([params],'"menutype":"main"','"menutype":"main_is_reserved_133C585"')
 WHERE [client_id] = 0
   AND (SELECT COUNT([id]) FROM [#__menu_types] WHERE [client_id] = 0 AND [menutype] = 'main') > 0;

UPDATE [#__menu_types]
   SET [menutype] = 'main_is_reserved_133C585'
 WHERE [client_id] = 0 
   AND [menutype] = 'main';

-- Step 2: What remains now are the main menu items, possibly with wrong
-- client_id if there was nothing hit by step 1 because there was no record in
-- the menu types table with client_id = 0.
UPDATE [#__menu]
   SET [client_id] = 1
 WHERE [menutype] = 'main';

-- Step 3: If we have menu items for the admin using menutype = "menu" and
-- having correct client_id = 1, we can be sure they belong to the admin menu
-- and so rename the menutype.
UPDATE [#__menu]
   SET [menutype] = 'main'
 WHERE [client_id] = 1 
   AND [menutype] = 'menu';

-- Step 4: If there is no user-defined menu type "menu" for the site, we can
-- assume that any menu items for that menu type belong to the admin.
-- Fix the client_id for those as it was done with the original version of this
-- schema update script here.
UPDATE [#__menu]
   SET [menutype] = 'main',
       [client_id] = 1
 WHERE [menutype] = 'menu'
   AND (SELECT COUNT([id]) FROM [#__menu_types] WHERE [client_id] = 0 AND [menutype] = 'menu') > 0;

-- Step 5: For the standard admin menu items of menutype "main" there is no record
-- in the menutype table on a clean Joomla installation. If there is one, it is a
-- mistake and it should be deleted. This is also the case with menu type "menu"
-- for the admin, for which we changed the menutype of the menu items in step 3.
DELETE FROM [#__menu_types]
 WHERE [client_id] = 1
   AND [menutype] IN ('main', 'menu');

-- End sync menutype for admin menu and set client_id correct

SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 462, 'plg_fields_calendar', 'plugin', 'calendar', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 463, 'plg_fields_checkboxes', 'plugin', 'checkboxes', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 464, 'plg_fields_color', 'plugin', 'color', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 465, 'plg_fields_editor', 'plugin', 'editor', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 466, 'plg_fields_imagelist', 'plugin', 'imagelist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 467, 'plg_fields_integer', 'plugin', 'integer', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 468, 'plg_fields_list', 'plugin', 'list', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 469, 'plg_fields_media', 'plugin', 'media', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 470, 'plg_fields_radio', 'plugin', 'radio', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 471, 'plg_fields_sql', 'plugin', 'sql', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 472, 'plg_fields_text', 'plugin', 'text', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 473, 'plg_fields_textarea', 'plugin', 'textarea', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 474, 'plg_fields_url', 'plugin', 'url', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 475, 'plg_fields_user', 'plugin', 'user', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0
UNION ALL
SELECT 476, 'plg_fields_usergrouplist', 'plugin', 'usergrouplist', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT #__extensions  OFF;

