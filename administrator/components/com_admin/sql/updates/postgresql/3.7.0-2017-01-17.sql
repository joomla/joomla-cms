-- Sync menutype for admin menu and set client_id correct

-- Note: This file had to be modified with Joomla 3.7.3 because the
-- original version made site menus disappear if there were menu types
-- "main" or "menu" defined for the site.

-- Step 1: If there is any user-defined menu and menu type "main" for the site
-- (client_id = 0), then change the menu type for the menu, any module and the
-- menu type to something hopefully not being used yet.
UPDATE "#__menu"
   SET "menutype" = 'main_is_reserved'
 WHERE "client_id" = 0
   AND "menutype" = 'main'
   AND (SELECT COUNT("id") FROM "#__menu_types" WHERE "client_id" = 0 AND "menutype" = 'main') > 0;

UPDATE "#__modules"
   SET "params" = REPLACE("params",'"menutype":"main"','"menutype":"main_is_reserved"')
 WHERE "client_id" = 0
   AND (SELECT COUNT("id") FROM "#__menu_types" WHERE "client_id" = 0 AND "menutype" = 'main') > 0;

UPDATE "#__menu_types"
   SET "menutype" = 'main_is_reserved'
 WHERE "client_id" = 0 
   AND "menutype" = 'main';

-- Step 2: What remains now are the main menu items, possibly with wrong
-- client_id if there was nothing hit by step 1 because there was no record in
-- the menu types table with client_id = 0.
UPDATE "#__menu"
   SET "client_id" = 1
 WHERE "menutype" = 'main';

-- Step 3: If we have menu items for the admin using menutype = "menu" and
-- having correct client_id = 1, we can be sure they belong to the admin menu
-- and so rename the menutype.
UPDATE "#__menu"
   SET "menutype" = 'main'
 WHERE "client_id" = 1 
   AND "menutype" = 'menu';

-- Step 4: If there is no user-defined menu type "menu" for the site, we can
-- assume that any menu items for that menu type belong to the admin.
-- Fix the client_id for those as it was done with the original version of this
-- schema update script here.
UPDATE "#__menu"
   SET "menutype" = 'main',
       "client_id" = 1
 WHERE "menutype" = 'menu'
   AND (SELECT COUNT("id") FROM "#__menu_types" WHERE "client_id" = 0 AND "menutype" = 'menu') > 0;

-- Step 5: For the standard admin menu items of menutype "main" there is no record
-- in the menutype table on a clean Joomla installation. If there is one, it is a
-- mistake and it should be deleted, but here we rename it so the admin can see
-- it and delete it then. This is also to be done with menu type "menu" for the
-- admin which we renamed before in step 3.
UPDATE "#__menu_types"
   SET "menutype" = 'main_to_be_deleted'
 WHERE "client_id" = 1
   AND "menutype" = 'main';

UPDATE "#__menu_types"
   SET "menutype" = 'menu_to_be_deleted'
 WHERE "client_id" = 1
   AND "menutype" = 'menu';
